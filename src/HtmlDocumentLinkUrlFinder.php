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
     * @var \DOMDocument
     */
    private $sourceDOM = null;

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
            $discoveredUrl = new NormalisedUrl($this->getAbsoluteUrlDeriver(
                $this->getUrlAttributeFromElement($element),
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
        $this->sourceDOM = null;
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
            $elements[] = trim($this->sourceDOM()->saveHtml($element));
        }

        return $elements;
    }

    private function getRawElements(): array
    {
        $elementsWithUrlAttributes = $this->getElementsWithUrlAttributes();
        $elements = [];

        foreach ($elementsWithUrlAttributes as $element) {
            $url = $this->getUrlAttributeFromElement($element);
            $discoveredUrl = new NormalisedUrl($this->getAbsoluteUrlDeriver(
                $url,
                (string)$this->configuration->getSourceUrl()
            )->getAbsoluteUrl());

            if ($this->isUrlInScope($discoveredUrl)) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    private function getUrlAttributeFromElement(\DOMElement $element): string
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

    private function getAbsoluteUrlDeriver(string $nonAbsoluteUrl, string $absoluteUrl): AbsoluteUrlDeriver
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

    private function sourceDOM(): \DOMDocument
    {
        if (is_null($this->sourceDOM)) {
            $this->sourceDOM = new \DOMDocument();
            $this->sourceDOM->recover = true;
            $this->sourceDOM->strictErrorChecking = false;
            $this->sourceDOM->validateOnParse = false;

            $source = $this->configuration->getSource();

            $characterSet = $source->getCharacterSet();
            $content = trim($source->getContent());

            if (!empty($characterSet)) {
                @$this->sourceDOM->loadHTML(
                    '<?xml encoding="'
                    . $this->configuration->getSource()->getCharacterSet()
                    . '">' . $content
                );
            } else {
                @$this->sourceDOM->loadHTML($content);
            }
        }

        return $this->sourceDOM;
    }

    private function getElementsWithinElement(\DOMElement $element): array
    {
        $elements = [];

        foreach ($element->childNodes as $childNode) {
            /* @var $childNode \DOMNode */
            if ($childNode->nodeType == XML_ELEMENT_NODE) {
                /* @var $childNode \DOMElement */
                $elements[] = $childNode;
                if ($childNode->hasChildNodes()) {
                    $elements = array_merge($elements, $this->getElementsWithinElement($childNode));
                }
            }
        }

        return $elements;
    }

    private function getElementsWithUrlAttributes(): array
    {
        if (is_null($this->elementsWithUrlAttributes)) {
            $this->elementsWithUrlAttributes = [];
            $elementScope = $this->configuration->getElementScope();

            $elements = empty($elementScope)
                ? $this->getAllElements()
                : $this->getScopedElements();

            foreach ($elements as $element) {
                /* @var $element \DOMElement */
                if (!$this->elementExcluder->isExcluded($element) && $this->hasUrlAttribute($element)) {
                    $this->elementsWithUrlAttributes[] = $element;
                }
            }
        }

        return $this->elementsWithUrlAttributes;
    }

    /**
     * @return \DOMElement[]
     */
    private function getAllElements(): array
    {
        $attributeScopeName = $this->configuration->getAttributeScopeName();
        $attributeScopeValue = $this->configuration->getAttributeScopeValue();
        $hasAttributeScope = !empty($attributeScopeName);

        $elements = $this->getElementsWithinElement($this->sourceDOM()->documentElement);

        if ($hasAttributeScope) {
            $attributeScopedElements = [];

            foreach ($elements as $element) {
                /* @var $element \DOMElement */
                if ($element->getAttribute($attributeScopeName) == $attributeScopeValue) {
                    $attributeScopedElements[] = $element;
                }
            }

            return $attributeScopedElements;
        }

        return $elements;
    }

    /**
     * @return \DOMElement[]
     */
    private function getScopedElements(): array
    {
        $attributeScopeName = $this->configuration->getAttributeScopeName();
        $attributeScopeValue = $this->configuration->getAttributeScopeValue();
        $hasAttributeScope = !empty($attributeScopeName);
        $elements = [];

        foreach ($this->configuration->getElementScope() as $tagName) {
            $domNodeList = $this->sourceDOM()->getElementsByTagName($tagName);
            $elementsByTagName = [];

            foreach ($domNodeList as $node) {
                /* @var $node \DOMElement */
                $includeNode = $hasAttributeScope
                    ? $node->getAttribute($attributeScopeName) == $attributeScopeValue
                    : true;

                if ($includeNode) {
                    $elementsByTagName[] = $node;
                }
            }

            $elements = array_merge($elements, $elementsByTagName);
        }

        return $elements;
    }

    private function hasUrlAttribute(\DOMElement $element): bool
    {
        $hasHrefAttribute = $element->hasAttribute(self::HREF_ATTRIBUTE_NAME);
        $hasSrcAttribute = $element->hasAttribute(self::SRC_ATTRIBUTE_NAME);

        if (!$hasHrefAttribute && !$hasSrcAttribute) {
            return false;
        }

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
            $this->baseUrl = $this->deriveBaseUrl();
        }

        return $this->baseUrl;
    }

    private function deriveBaseUrl(): string
    {
        /* @var WebPage $webPage */
        $webPage = $this->configuration->getSource();

        $webPageBaseUrl = $webPage->getBaseUrl();

        return (empty($webPageBaseUrl)) ? $this->configuration->getSourceUrl() : $webPageBaseUrl;
    }
}
