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
    private $anchors = null;
    
    
    /**
     *
     * @var array
     */
    private $urls = null;
    
    
    /**
     *
     * @var array
     */
    private $scope = null;
    
    
    /**
     *
     * @var \webignition\Url\ScopeComparer
     */
    private $scopeComparer = null;
    
    
    
    /**
     * 
     * @param \webignition\Url\ScopeComparer $scopeComparer
     */
    public function setScopeComparer(\webignition\Url\ScopeComparer $scopeComparer) {
        $this->scopeComparer = $scopeComparer;
    }
    
    
    /**
     * 
     * @return \webignition\Url\ScopeComparer
     */
    public function getScopeComparer() {
        if (is_null($this->scopeComparer)) {
            $this->scopeComparer = new ScopeComparer();
        }
        
        return $this->scopeComparer;
    }
    
    
    
    /**
     * 
     * @param string $scope
     */
    public function setScope($scope) {
        if (is_string($scope)) {
            $this->scope = array(new NormalisedUrl($scope));
        }
        
        if (is_array($scope)) {
            $this->scope = array();
            foreach ($scope as $url) {
                $this->scope[] = new NormalisedUrl($url);
            }                
        }
        
        
        $this->reset();
    }
    
    
    /**
     * 
     * @return string
     */
    public function getScope() {
        return $this->scope;
    }
    
    
    
    /**
     *
     * @param string $sourceContent 
     */
    public function setSourceContent($sourceContent) {
        $this->sourceContent = $sourceContent;
        $this->reset();
    }
    
    
    /**
     *
     * @param string $sourceUrl 
     */
    public function setSourceUrl($sourceUrl) {
        $this->sourceUrl = new NormalisedUrl($sourceUrl);
        $this->reset();
    }
    
    
    /**
     *
     * @return string
     */
    public function getSourceUrl() {
        return $this->sourceUrl;
    }
    
    
    /**
     * Reset to default state
     *  
     */
    protected function reset() {
        $this->resetUrls();
    }
    
    
    /**
     * Forget all URLs found in the current page 
     */
    protected function resetUrls() {
        $this->urls = null;
    }
    
    /**
     * Set the collection of urls to be an empty collection
     * 
     */
    protected function clearUrls() {
        $this->urls = array();
    }
    
    
    /**
     *
     * @return array 
     */
    public function getUrls() {
        if (!$this->hasUrls()) {            
            $this->urls = array();
            
            $anchors = $this->anchors();
            
            for ($anchorIndex = 0; $anchorIndex < $anchors->length; $anchorIndex++) {
                if ($this->hasHref($anchors->item($anchorIndex))) {
                    $discoveredUrl = new NormalisedUrl($this->getAbsoluteUrlDeriver(
                        $anchors->item($anchorIndex)->getAttribute('href'),
                        (string)$this->sourceUrl
                    )->getAbsoluteUrl());

                    if ($this->isUrlInScope($discoveredUrl)) {
                        $this->addUrl((string)$discoveredUrl);
                    }
                }
            }
        }
        
        return $this->urls;       
    }
    
    
    private function isUrlInScope($discoveredUrl) {
        if (!$this->hasScope()) {
            return true;
        }
        
        foreach ($this->scope as $scopeUrl) {
            if ($this->getScopeComparer()->isInScope($scopeUrl, $discoveredUrl)) {
                return true;
            }
        }
        
        return false;
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
    protected function hasScope() {
        return !is_null($this->getScope());
    }
    
    
    /**
     *
     * @return boolean
     */
    public function hasUrls() {
        return !is_null($this->urls);
    }
    

    /**
     * Add a single URL to the existing list of URLs found in the HTML document
     * 
     * @param string $url 
     */
    protected function addUrl($url) {        
        if (is_string($url) && !$this->contains($url)) {
            $this->urls[] = $url;
        }
    }
    
    
    /**
     *
     * @param string $url
     * @return boolean 
     */
    private function contains($url) {
        $normalisedUrl = new \webignition\Url\Url($url);
        return in_array((string)$normalisedUrl, $this->urls);
    }
    
    
    /**
     *
     * @return \DOMNodeList
     */
    private function anchors() {
        if (is_null($this->anchors)) {
            $this->anchors = $this->sourceDOM()->getElementsByTagName('a');
        }
        
        return $this->anchors;
    }
    
    
    /**
     *
     * @param \DOMElement $anchor
     * @return boolean
     */
    private function hasHref(\DOMElement $anchor) {
        return trim($anchor->getAttribute('href')) != '';
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
        

}