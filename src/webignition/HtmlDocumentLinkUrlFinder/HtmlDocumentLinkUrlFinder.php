<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\Url\ScopeComparer;

/**
 * Finds links in an HTML Document
 * 
 * @package webignition\HtmlDocumentLinkUrlFinder
 *
 */
class HtmlDocumentLinkUrlFinder {
    
    const HREF_ATTRIBUTE_NAME  = 'href';
    const SRC_ATTRIBUTE_NAME  = 'src';
    
    const BASE_ELEMENT_NAME = 'base';
    
    private $urlAttributeNames = array(
        self::HREF_ATTRIBUTE_NAME, 
        self::SRC_ATTRIBUTE_NAME
    );
    
    private $ignoredElementNames = array(
        self::BASE_ELEMENT_NAME
    );

    
    /**
     *
     * @var string
     */
    private $sourceContent = null;
    
    
    /**
     *
     * @var string
     */
    private $sourceUrl = null;
    
    
    /**
     *
     * @var \DOMDocument
     */
    private $sourceDOM = null;
    
    
    /**
     *
     * @var array
     */
    private $elementsWithUrlAttributes = null;
    
    
    /**
     *
     * @var array
     */
    private $urlScope = null;
    
    
    /**
     *
     * @var array
     */
    private $elementScope = null;
    
    
    /**
     *
     * @var \webignition\Url\ScopeComparer
     */
    private $urlScopeComparer = null;
    
    
    /**
     *
     * @var string
     */
    private $baseUrl = null;
    
    /**
     * 
     * @param \webignition\Url\ScopeComparer $scopeComparer
     */
    public function setUrlScopeComparer(\webignition\Url\ScopeComparer $scopeComparer) {
        $this->urlScopeComparer = $scopeComparer;
    }
    
    
    /**
     * 
     * @return \webignition\Url\ScopeComparer
     */
    public function getUrlScopeComparer() {
        if (is_null($this->urlScopeComparer)) {
            $this->urlScopeComparer = new ScopeComparer();
        }
        
        return $this->urlScopeComparer;
    }
    
    
    
    /**
     * 
     * @param string|array $scope
     */
    public function setUrlScope($scope) {        
        if (is_string($scope)) {
            $this->urlScope = array(new NormalisedUrl($scope));
        }
        
        if (is_array($scope)) {
            $this->urlScope = array();
            foreach ($scope as $url) {
                $this->urlScope[] = new NormalisedUrl($url);
            }                
        }
    }
    
    
    /**
     * 
     * @return array
     */
    public function getUrlScope() {
        return $this->urlScope;
    }
    
    
    /**
     * 
     * @param string|array $context
     */
    public function setElementScope($scope) {        
        if (is_string($scope)) {
            $this->elementScope = array($scope);
        }
        
        if (is_array($scope)) {
            $this->elementScope = $scope;                
        }
        
        if (is_array($this->elementScope)) {
            foreach ($this->elementScope as $index => $nodeName) {
                $this->elementScope[$index] = strtolower($nodeName);
            }
        }
    }
    
    
    /**
     * 
     * @return array
     */
    public function getElementScope() {
        return $this->elementScope;
    }
    
    
    
    /**
     *
     * @param string $sourceContent 
     */
    public function setSourceContent($sourceContent) {
        $this->sourceContent = $sourceContent;
        $this->elementsWithUrlAttributes = null;
    }
    
    
    /**
     *
     * @param string $sourceUrl 
     */
    public function setSourceUrl($sourceUrl) {
        $this->sourceUrl = new NormalisedUrl($sourceUrl);
        $this->elementsWithUrlAttributes = null;
    }
    
    
    /**
     *
     * @return string
     */
    public function getSourceUrl() {
        return $this->sourceUrl;
    }
    
    
    /**
     *
     * @return array 
     */
    public function getUniqueUrls() {        
        $allUrls = $this->getAllUrls();
        $urls = array();
        
        foreach ($allUrls as $url) {
            if (!in_array($url, $urls)) {
                $urls[] = $url;
            }
        }
        
        return $urls;
    }
    
    
    /**
     * 
     * @return array
     */
    public function getAllUrls() {        
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
     * 
     * @return array
     */
    public function getAll() {
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
    
    
    /**
     * 
     * @return array
     */
    public function getElements() {
        $elements = array();
        $rawElements = $this->getRawElements();
        
        foreach ($rawElements as $element) {
            $elements[] = trim($this->sourceDOM()->saveHtml($element));
        }
        
        return $elements;
    }
    
    
    /**
     * 
     * @return array
     */
    private function getRawElements() {
        $elementsWithUrlAttributes = $this->getElementsWithUrlAttributes();
        $elements = array();

        foreach ($elementsWithUrlAttributes as $element) {
            if (!$this->isElementInContext($element)) {
                continue;
            }
            
            $url = $this->getUrlAttributeFromElement($element);            
            $discoveredUrl = new NormalisedUrl($this->getAbsoluteUrlDeriver(
                $url,
                (string)$this->sourceUrl
            )->getAbsoluteUrl());

            if ($this->isUrlInScope($discoveredUrl)) {                
                $elements[] = $element;
            }            
        }
        
        return $elements;          
    }
    
    
    /**
     * 
     * @param \DOMElement $element
     * @return string
     */
    private function getUrlAttributeFromElement(\DOMElement $element) {
        foreach ($this->urlAttributeNames as $attributeName) {
            if ($element->hasAttribute($attributeName)) {
                return $element->getAttribute($attributeName);
            }
        }
        
        return null;        
    }
    
    
    /**
     * 
     * @param \webignition\Url\Url $discoveredUrl
     * @return boolean
     */
    private function isUrlInScope(\webignition\Url\Url $discoveredUrl) {
        if (!$this->hasScope()) {
            return true;
        }
        
        foreach ($this->urlScope as $scopeUrl) {
            if ($this->getUrlScopeComparer()->isInScope($scopeUrl, $discoveredUrl)) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * 
     * @param \DOMElement $element
     * @return boolean
     */
    private function isElementInContext(\DOMElement $element) {
        if (!is_array($this->elementScope)) {
            return true;
        }
        
        if (count($this->elementScope) === 0) {
            return true;
        }
        
        return in_array($element->nodeName, $this->elementScope);
    }
    
    
    /**
     * 
     * @param string $nonAbsoluteUrl
     * @param string $absoluteUrl
     * @return \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver
     */
    private function getAbsoluteUrlDeriver($nonAbsoluteUrl, $absoluteUrl) {
        return new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
            $nonAbsoluteUrl,
            $absoluteUrl
        );
    }
    
    
    /**
     * 
     * @return boolean
     */
    private function hasScope() {
        return !is_null($this->getUrlScope());
    }
    
    
    /**
     *
     * @return boolean
     */
    public function hasUrls() {
        return count($this->getUniqueUrls()) > 0;
    }
    
    
    /**
     *
     * @return \DOMDocument
     */
    private function sourceDOM() {
        if (is_null($this->sourceDOM)) {
            $this->sourceDOM = new \DOMDocument();
            $this->sourceDOM->recover = true;
            $this->sourceDOM->strictErrorChecking = false;            
            $this->sourceDOM->validateOnParse = false;
            
            @$this->sourceDOM->loadHTML($this->sourceContent);
        }
        
        return $this->sourceDOM;
    }
    
    
    /**
     * 
     * @param \DOMElement $element
     * @return array
     */
    private function getElementsWithinElement(\DOMElement $element) {
        $elements = array();
        
        foreach ($element->childNodes as $childNode) {
            /* @var $childNode \DOMNode */            
            if ($childNode->nodeType == XML_ELEMENT_NODE) {
                $elements[] = $childNode;
                if ($childNode->hasChildNodes()) {                    
                    $elements = array_merge($elements, $this->getElementsWithinElement($childNode));
                }
            }
            
        }
        
        return $elements;
    }
    
    
    /**
     * 
     * @return array
     */
    private function getElementsWithUrlAttributes() {
        if (is_null($this->elementsWithUrlAttributes)) {
            $this->elementsWithUrlAttributes = array();
            $elements = $this->getElementsWithinElement($this->sourceDOM()->documentElement);

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
     * 
     * @param \DOMElement $element
     * @return boolean
     */
    private function isIgnoredElement(\DOMElement $element) {
        return in_array($element->nodeName, $this->ignoredElementNames);
    }
    
    
    /**
     * 
     * @param \DOMElement $element
     * @return boolean
     */
    private function hasUrlAttribute(\DOMElement $element) {
        foreach ($this->urlAttributeNames as $attributeName) {
            if ($element->hasAttribute($attributeName)) {
                return true;
            }
        }
        
        return false;
    }
    
    
    
    private function getBaseUrl() {
        if (is_null($this->baseUrl)) {
            $baseElement = $this->getBaseElement();
            if (is_null($baseElement)) {
                $this->baseUrl = (string)$this->sourceUrl;
            } else {
                $absoluteUrlDeriver = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
                    $baseElement->getAttribute('href'),
                    (string)$this->sourceUrl                        
                );
                
                $this->baseUrl = (string)$absoluteUrlDeriver->getAbsoluteUrl();
            }    
        }
        
        return $this->baseUrl;
    }
    
   
    /**
     * 
     * @return \DOMElement|null
     */
    private function getBaseElement() {
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