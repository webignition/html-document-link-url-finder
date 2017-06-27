<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\NormalisedUrl\NormalisedUrl;
use webignition\Url\ScopeComparer;
use webignition\Url\Url;

/**
 * Finds links in an HTML Document
 *
 * @package webignition\HtmlDocumentLinkUrlFinder
 *
 */
class HtmlDocumentLinkUrlFinder
{
    const HREF_ATTRIBUTE_NAME  = 'href';
    const SRC_ATTRIBUTE_NAME  = 'src';

    const BASE_ELEMENT_NAME = 'base';

    private $ignoredElementNames = array(
        self::BASE_ELEMENT_NAME
    );

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
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        if (is_null($this->configuration)) {
            $this->configuration = new Configuration();
        }

        return $this->configuration;
    }

    /**
     * @return ScopeComparer
     */
    public function getUrlScopeComparer()
    {
        if (is_null($this->urlScopeComparer)) {
            $this->urlScopeComparer = new ScopeComparer();
        }

        return $this->urlScopeComparer;
    }

    /**
     *
     * @return array
     */
    public function getUniqueUrls()
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        $allUrls = $this->getAllUrls();
        $urls = array();

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

    /**
     * @param string $url
     *
     * @return string
     */
    private function getUniquenessComparisonUrl($url)
    {
        $urlObject = new Url($url);

        if (!$urlObject->hasFragment()) {
            return $url;
        }

        $urlObject->setFragment(null);
        return (string)$urlObject;
    }

    /**
     *
     * @return array
     */
    public function getAllUrls()
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        if (!$this->getConfiguration()->hasSourceContent()) {
            return [];
        }

        $urls = array();
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
     */
    public function getAll()
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        if (!$this->getConfiguration()->hasSourceContent()) {
            return [];
        }

        $urls = $this->getAllUrls();
        $elements = $this->getElements();

        $result = array();

        foreach ($urls as $index => $url) {
            $result[] = array(
                'url' => $url,
                'element' => $elements[$index]
            );
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
     */
    public function getElements()
    {
        if ($this->getConfiguration()->requiresReset()) {
            $this->reset();
        }

        if (!$this->getConfiguration()->hasSourceContent()) {
            return [];
        }

        $elements = array();
        $rawElements = $this->getRawElements();

        foreach ($rawElements as $element) {
            $elements[] = trim($this->sourceDOM()->saveHtml($element));
        }

        return $elements;
    }

    /**
     * @return array
     */
    private function getRawElements()
    {
        $elementsWithUrlAttributes = $this->getElementsWithUrlAttributes();
        $elements = array();

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

    /**
     * @param \DOMElement $element
     *
     * @return string
     */
    private function getUrlAttributeFromElement(\DOMElement $element)
    {
        if ($element->hasAttribute(self::HREF_ATTRIBUTE_NAME)) {
            return $element->getAttribute(self::HREF_ATTRIBUTE_NAME);
        }

        return $element->getAttribute(self::SRC_ATTRIBUTE_NAME);
    }

    /**
     * @param Url $discoveredUrl
     *
     * @return boolean
     */
    private function isUrlInScope(Url $discoveredUrl)
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

    /**
     * @param string $nonAbsoluteUrl
     * @param string $absoluteUrl
     *
     * @return AbsoluteUrlDeriver
     */
    private function getAbsoluteUrlDeriver($nonAbsoluteUrl, $absoluteUrl)
    {
        return new AbsoluteUrlDeriver(
            $nonAbsoluteUrl,
            $absoluteUrl
        );
    }

    /**
     * @return boolean
     */
    public function hasUrls()
    {
        return count($this->getUniqueUrls()) > 0;
    }

    /**
     * @return \DOMDocument
     */
    private function sourceDOM()
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

    /**
     * @param \DOMElement $element
     * @return array
     */
    private function getElementsWithinElement(\DOMElement $element)
    {
        $elements = array();

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
     */
    private function getElementsWithUrlAttributes()
    {
        if (is_null($this->elementsWithUrlAttributes)) {
            $this->elementsWithUrlAttributes = array();
            $elementScope = $this->getConfiguration()->getElementScope();

            $elements = empty($elementScope)
                ? $this->getAllElements()
                : $this->getScopedElements();

            foreach ($elements as $element) {
                /* @var $element \DOMElement */
                if (!$this->isIgnoredElement($element) && $this->hasUrlAttribute($element)) {
                    $this->elementsWithUrlAttributes[] = $element;
                }
            }
        }

        return $this->elementsWithUrlAttributes;
    }

    /**
     * @return \DOMElement[]
     */
    private function getAllElements()
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
     */
    private function getScopedElements()
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

    /**
     * @param \DOMElement $element
     *
     * @return boolean
     */
    private function isIgnoredElement(\DOMElement $element)
    {
        return in_array($element->nodeName, $this->ignoredElementNames);
    }

    /**
     * @param \DOMElement $element
     *
     * @return boolean
     */
    private function hasUrlAttribute(\DOMElement $element)
    {
        if ($element->hasAttribute(self::HREF_ATTRIBUTE_NAME)) {
            return $this->hasNonEmptyUrlAttribute($element, self::HREF_ATTRIBUTE_NAME);
        }

        if ($element->hasAttribute(self::SRC_ATTRIBUTE_NAME)) {
            return $this->hasNonEmptyUrlAttribute($element, self::HREF_ATTRIBUTE_NAME);
        }

        return false;
    }

    /**
     * @param \DOMElement $element
     * @param string $attributeName
     *
     * @return bool
     */
    private function hasNonEmptyUrlAttribute(\DOMElement $element, $attributeName)
    {
        return $this->getConfiguration()->getIgnoreEmptyHref()
            ? !empty(trim($element->getAttribute($attributeName)))
            : true;
    }

    private function getBaseUrl()
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
     */
    private function getBaseElement()
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
