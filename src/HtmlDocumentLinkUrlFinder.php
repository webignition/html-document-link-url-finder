<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use QueryPath\Exception as QueryPathException;
use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\NormalisedUrl\NormalisedUrl;
use webignition\Url\ScopeComparer;
use webignition\Url\Url;

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
     *
     * @throws QueryPathException
     */
    public function getUniqueUrls(): array
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        $allUrls = $this->getAllUrls();
        $urls = [];

        foreach ($allUrls as $url) {
            if ($this->getConfiguration()->getIgnoreFragmentInUrlComparison()) {
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
     *
     * @throws QueryPathException
     */
    public function getAllUrls(): array
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        if (!$this->getConfiguration()->hasSourceContent()) {
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

    /**
     * @return array
     *
     * @throws QueryPathException
     */
    public function getAll(): array
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        if (!$this->getConfiguration()->hasSourceContent()) {
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
        $this->getConfiguration()->clearReset();
    }

    /**
     * @return array
     *
     * @throws QueryPathException
     */
    public function getElements(): array
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        if (!$this->getConfiguration()->hasSourceContent()) {
            return [];
        }

        $elements = [];
        $rawElements = $this->getRawElements();

        foreach ($rawElements as $element) {
            $elements[] = trim($this->sourceDOM()->saveHtml($element));
        }

        return $elements;
    }

    /**
     * @return array
     *
     * @throws QueryPathException
     */
    private function getRawElements(): array
    {
        $elementsWithUrlAttributes = $this->getElementsWithUrlAttributes();
        $elements = [];

        foreach ($elementsWithUrlAttributes as $element) {
            $url = $this->getUrlAttributeFromElement($element);
            $discoveredUrl = new NormalisedUrl($this->getAbsoluteUrlDeriver(
                $url,
                (string)$this->getConfiguration()->getSourceUrl()
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
        if (!$this->getConfiguration()->hasUrlScope()) {
            return true;
        }

        foreach ($this->getConfiguration()->getUrlScope() as $scopeUrl) {
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

    /**
     * @return bool
     *
     * @throws QueryPathException
     */
    public function hasUrls(): bool
    {
        return count($this->getUniqueUrls()) > 0;
    }

    /**
     * @return \DOMDocument
     *
     * @throws QueryPathException
     */
    private function sourceDOM(): \DOMDocument
    {
        if (is_null($this->sourceDOM)) {
            $this->sourceDOM = new \DOMDocument();
            $this->sourceDOM->recover = true;
            $this->sourceDOM->strictErrorChecking = false;
            $this->sourceDOM->validateOnParse = false;

            $source = $this->getConfiguration()->getSource();

            $characterSet = $source->getCharacterSet();
            $content = trim($source->getContent());

            if (!empty($characterSet)) {
                @$this->sourceDOM->loadHTML(
                    '<?xml encoding="'
                    . $this->getConfiguration()->getSource()->getCharacterSet()
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

    /**
     * @return array
     *
     * @throws QueryPathException
     */
    private function getElementsWithUrlAttributes(): array
    {
        if (is_null($this->elementsWithUrlAttributes)) {
            $this->elementsWithUrlAttributes = [];
            $elementScope = $this->getConfiguration()->getElementScope();

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
     *
     * @throws QueryPathException
     */
    private function getAllElements(): array
    {
        $attributeScopeName = $this->getConfiguration()->getAttributeScopeName();
        $attributeScopeValue = $this->getConfiguration()->getAttributeScopeValue();
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
     *
     * @throws QueryPathException
     */
    private function getScopedElements(): array
    {
        $attributeScopeName = $this->getConfiguration()->getAttributeScopeName();
        $attributeScopeValue = $this->getConfiguration()->getAttributeScopeValue();
        $hasAttributeScope = !empty($attributeScopeName);
        $elements = [];

        foreach ($this->getConfiguration()->getElementScope() as $tagName) {
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
        return $this->getConfiguration()->getIgnoreEmptyHref()
            ? !empty(trim($attributeValue))
            : true;
    }

    /**
     * @return string
     *
     * @throws QueryPathException
     */
    private function getBaseUrl(): string
    {
        if (is_null($this->baseUrl)) {
            $baseElement = $this->getBaseElement();
            if (is_null($baseElement)) {
                $this->baseUrl = (string)$this->getConfiguration()->getSourceUrl();
            } else {
                $absoluteUrlDeriver = new AbsoluteUrlDeriver(
                    $baseElement->getAttribute('href'),
                    (string)$this->getConfiguration()->getSourceUrl()
                );

                $this->baseUrl = (string)$absoluteUrlDeriver->getAbsoluteUrl();
            }
        }

        return $this->baseUrl;
    }

    /**
     * @return \DOMElement|null
     *
     * @throws QueryPathException
     */
    private function getBaseElement(): ?\DOMElement
    {
        $baseElements = $this->sourceDOM()->getElementsByTagName('base');
        if ($baseElements->length !== 1) {
            return null;
        }

        $baseElement = $baseElements->item(0);
        if (!$baseElement->hasAttribute('href')) {
            return null;
        }

        return $baseElement;
    }
}
