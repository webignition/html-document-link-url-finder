<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\NormalisedUrl\NormalisedUrl;
use webignition\Url\ScopeComparer;
use webignition\Url\Url;
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
    public function getUniqueUrls(): array
    {
        if ($this->configuration->requiresReset()) {
            $this->reset();
        }

        $allUrls = $this->getAllUrls();
        $urls = [];

        foreach ($allUrls as $url) {
            if ($this->configuration->getIgnoreFragmentInUrlComparison()) {
                $url = $this->getUniquenessComparisonUrl($url);
            }

            if (!in_array($url, $urls)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    private function getUniquenessComparisonUrl(string $url): string
    {
        $urlObject = new Url($url);

        if (!$urlObject->hasFragment()) {
            return $url;
        }

        $urlObject->setFragment(null);
        return (string)$urlObject;
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

        foreach ($elements as $element) {
            $discoveredUrl = new NormalisedUrl($this->createAbsoluteUrlDeriver(
                $this->getUrlValueFromElement($element),
                $this->getBaseUrl()
            )->getAbsoluteUrl());

            $urls[] = (string)$discoveredUrl;
        }

        return $urls;
    }

    public function getAll(): array
    {
        if ($this->configuration->requiresReset()) {
            $this->reset();
        }

        if (!$this->configuration->hasSourceContent()) {
            return [];
        }

        $urls = $this->getAllUrls();
        $elements = $this->getElements();

        $result = [];

        foreach ($urls as $index => $url) {
            $result[] = [
                'url' => $url,
                'element' => $elements[$index]
            ];
        }

        return $result;
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

        foreach ($elementsWithUrlAttributes as $element) {
            $url = $this->getUrlValueFromElement($element);
            $discoveredUrl = new NormalisedUrl($this->createAbsoluteUrlDeriver(
                $url,
                (string)$this->configuration->getSourceUrl()
            )->getAbsoluteUrl());

            if ($this->isUrlInScope($discoveredUrl)) {
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

    private function isUrlInScope(Url $discoveredUrl): bool
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

    private function createAbsoluteUrlDeriver(string $nonAbsoluteUrl, string $absoluteUrl): AbsoluteUrlDeriver
    {
        return new AbsoluteUrlDeriver(
            $nonAbsoluteUrl,
            $absoluteUrl
        );
    }

    public function hasUrls(): bool
    {
        return count($this->getUniqueUrls()) > 0;
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
