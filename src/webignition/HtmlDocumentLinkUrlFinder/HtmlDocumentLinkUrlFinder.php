<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

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
        $this->sourceUrl = $sourceUrl;
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
    public function urls() {
        if (!$this->hasUrls()) {            
            $this->urls = array();
            
            $anchors = $this->anchors();
            
            for ($anchorIndex = 0; $anchorIndex < $anchors->length; $anchorIndex++) {
                if ($this->hasHref($anchors->item($anchorIndex))) {                    
                    $href = new \webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver(
                        $anchors->item($anchorIndex)->getAttribute('href'),
                        $this->sourceUrl
                    );
                    
                    $this->addUrl((string)$href->getAbsoluteUrl());
                }
            }
        }
        
        return $this->urls;       
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