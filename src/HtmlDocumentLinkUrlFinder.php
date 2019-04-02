<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use Psr\Http\Message\UriInterface;
use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\Uri\Normalizer;
use webignition\Uri\ScopeComparer;
use webignition\Uri\Uri;
use webignition\WebResource\WebPage\WebPage;

class HtmlDocumentLinkUrlFinder
{
    const HREF_ATTRIBUTE_NAME  = 'href';
    const SRC_ATTRIBUTE_NAME  = 'src';

    /**
     * @var array
     */
    private $elementsWithUrlAttributes = null;

    /**
     * @var ScopeComparer
     */
    private $urlScopeComparer = null;

    /**
     * @var string
     */
    private $baseUrl = null;

    /**
     * @var Configuration
     */
    private $configuration = null;

    /**
     * @var ElementExcluder
     */
    private $elementExcluder;

    public function __construct()
    {
        $this->elementExcluder = new ElementExcluder();
        $this->configuration = new Configuration();
        $this->urlScopeComparer = new ScopeComparer();
    }

    public function getLinkCollection(): LinkCollection
    {
        $links = [];

        if (!$this->configuration->hasSourceContent()) {
            return new LinkCollection($links);
        }

        $urls = $this->getAllUrls();
        $elements = $this->getRawElements();

        foreach ($urls as $index => $url) {
            $links[] = new Link(
                new Uri($url),
                $elements[$index]
            );
        }

        return new LinkCollection($links);
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getUrlScopeComparer(): ScopeComparer
    {
        return $this->urlScopeComparer;
    }

    /**
     * @return string[]
     */
    public function getAllUrls(): array
    {
        if ($this->configuration->requiresReset()) {
            $this->reset();
        }

        if (!$this->configuration->hasSourceContent()) {
            return [];
        }

        $urls = [];
        $elements = $this->getRawElements();

        if (empty($elements)) {
            return [];
        }

        $baseUri = new Uri($this->getBaseUrl());

        foreach ($elements as $element) {
            $uri = AbsoluteUrlDeriver::derive(
                $baseUri,
                new Uri($this->getUrlValueFromElement($element))
            );

            $uri = Normalizer::normalize($uri);

            $urls[] = (string) $uri;
        }

        return $urls;
    }

    private function reset()
    {
        $this->elementsWithUrlAttributes = null;
        $this->configuration->clearReset();
    }

    public function getElements(): array
    {
        if ($this->configuration->requiresReset()) {
            $this->reset();
        }

        if (!$this->configuration->hasSourceContent()) {
            return [];
        }

        $elements = [];
        $rawElements = $this->getRawElements();

        foreach ($rawElements as $element) {
            /* @var \DOMElement $element */
            $elements[] = trim($element->ownerDocument->saveHTML($element));
        }

        return $elements;
    }

    private function getRawElements(): array
    {
        $elementsWithUrlAttributes = $this->getElementsWithUrlAttributes();
        $elements = [];

        if (empty($elementsWithUrlAttributes)) {
            return [];
        }

        $baseUri = new Uri($this->configuration->getSourceUrl());

        foreach ($elementsWithUrlAttributes as $element) {
            $discoveredUri = AbsoluteUrlDeriver::derive(
                $baseUri,
                new Uri($this->getUrlValueFromElement($element))
            );

            $discoveredUri = Normalizer::normalize($discoveredUri);

            if ($this->isUrlInScope($discoveredUri)) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    private function getUrlValueFromElement(\DOMElement $element): string
    {
        if ($element->hasAttribute(self::HREF_ATTRIBUTE_NAME)) {
            return $element->getAttribute(self::HREF_ATTRIBUTE_NAME);
        }

        return $element->getAttribute(self::SRC_ATTRIBUTE_NAME);
    }

    private function isUrlInScope(UriInterface $discoveredUrl): bool
    {
        if (!$this->configuration->hasUrlScope()) {
            return true;
        }

        foreach ($this->configuration->getUrlScope() as $scopeUrl) {
            if ($this->getUrlScopeComparer()->isInScope($scopeUrl, $discoveredUrl)) {
                return true;
            }
        }

        return false;
    }

    private function getElementsWithUrlAttributes(): array
    {
        if (empty($this->elementsWithUrlAttributes)) {
            $elements = $this->findElementsWithUrlAttributes();
            $filteredElements = $this->filterElements($elements);

            $this->elementsWithUrlAttributes = $filteredElements;
        }

        return $this->elementsWithUrlAttributes;
    }

    /**
     * @param \DOMElement[] $elements
     *
     * @return \DOMElement[]
     */
    private function filterElements(array $elements): array
    {
        $filteredElements = [];

        foreach ($elements as $element) {
            $includeElement = $this->isElementInNameScope($element);
            $includeElement = $includeElement && !$this->elementExcluder->isExcluded($element);
            $includeElement = $includeElement && $this->hasUrlAttribute($element);
            $includeElement = $includeElement && $this->isElementInAttributeScope($element);

            if ($includeElement) {
                $filteredElements[] = $element;
            }
        }

        return $filteredElements;
    }

    private function isElementInNameScope(\DOMElement $element): bool
    {
        $elementScope = $this->configuration->getElementScope();

        if (empty($elementScope)) {
            return true;
        }

        $isInScope = false;

        foreach ($elementScope as $nodeName) {
            /* @var \DOMElement $element */
            if ($element->nodeName === $nodeName) {
                $isInScope = true;
            }
        }

        return $isInScope;
    }

    private function isElementInAttributeScope(\DOMElement $element): bool
    {
        $attributeScopeName = $this->configuration->getAttributeScopeName();
        $attributeScopeValue = $this->configuration->getAttributeScopeValue();
        $hasAttributeScope = !empty($attributeScopeName);

        if (!$hasAttributeScope) {
            return true;
        }

        return $element->getAttribute($attributeScopeName) == $attributeScopeValue;
    }


    private function findElementsWithUrlAttributes(): array
    {
        /* @var WebPage $webPage */
        $webPage = $this->configuration->getSource();

        $inspector = $webPage->getInspector();

        return $inspector->querySelectorAll('[href], [src]');
    }

    private function hasUrlAttribute(\DOMElement $element): bool
    {
        $hasHrefAttribute = $element->hasAttribute(self::HREF_ATTRIBUTE_NAME);
        $hasSrcAttribute = $element->hasAttribute(self::SRC_ATTRIBUTE_NAME);

        $attributeValue = '';

        if ($hasHrefAttribute) {
            $attributeValue = $element->getAttribute(self::HREF_ATTRIBUTE_NAME);
        } elseif ($hasSrcAttribute) {
            $attributeValue = $element->getAttribute(self::SRC_ATTRIBUTE_NAME);
        }

        if (!$this->hasNonEmptyUrlAttribute($attributeValue)) {
            return false;
        }

        if ($this->looksLikeConcatenatedJsString($attributeValue)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the URL value from an element attribute looks like a concatenated JS string
     *
     * Some documents contain script elements which concatenate JS values together to make URLs for elements that
     * are inserted into the DOM. This is all fine.
     * e.g. VimeoList.push('<img src="' + value['ListImage'] + '" />');
     *
     * A subset of such documents are badly-formed such that the script contents are not recognised by the DOM parser
     * and end up as element nodes in the DOM
     * e.g. the above would result in a <img src="' + value['ListImage'] + '" /> element present in the DOM.
     *
     * This applies to both the \DOMDocument parser and the W3C HTML validation parser. It is assumed both parsers
     * are not identically buggy.
     *
     * We need to check if a URL value looks like a concatenated JS string so that we can exclude them.
     *
     * Example URL value: ' + value['ListImage'] + '
     * Pseudopattern: START'<whitespace?><plus char><whitespace?><anything><whitespace?><plus char><whitespace?>'END
     *
     * @param string $url
     *
     * @return bool
     */
    private function looksLikeConcatenatedJsString(string $url): bool
    {
        $patternBody = "^'\s+\+\s+.*\s+\+\s+'$";
        $pattern = '/'.$patternBody.'/i';

        return preg_match($pattern, $url) > 0;
    }

    private function hasNonEmptyUrlAttribute(string $attributeValue): bool
    {
        return $this->configuration->getIgnoreEmptyHref()
            ? !empty(trim($attributeValue))
            : true;
    }

    private function getBaseUrl(): string
    {
        if (is_null($this->baseUrl)) {
            /* @var WebPage $webPage */
            $webPage = $this->configuration->getSource();

            $webPageBaseUrl = $webPage->getBaseUrl();

            $this->baseUrl = (empty($webPageBaseUrl)) ? $this->configuration->getSourceUrl() : $webPageBaseUrl;
        }

        return $this->baseUrl;
    }
}
