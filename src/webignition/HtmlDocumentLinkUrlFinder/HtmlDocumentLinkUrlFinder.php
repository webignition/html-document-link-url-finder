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
     * @var \DOMNodeList
     */
    private $urls = null;
    
    
    /**
     *
     * @param string $sourceContent 
     */
    public function setSourceContent($sourceContent) {
        $this->sourceContent = $sourceContent;
    }
    
    
    /**
     *
     * @param string $sourceUrl 
     */
    public function setSourceUrl($sourceUrl) {
        $this->sourceUrl = $sourceUrl;
    }
    
    
    /**
     *
     * @return array 
     */
    public function urls() {
        if (is_null($this->urls)) {
            $this->urls = array();
            
            $anchors = $this->anchors();
            
            for ($anchorIndex = 0; $anchorIndex < $anchors->length; $anchorIndex++) {
                if ($this->hasHref($anchors->item($anchorIndex))) {                    
                    $href = new \webignition\HtmlDocumentLinkUrlFinder\DocumentHref(
                        $anchors->item($anchorIndex)->getAttribute('href'),
                        $this->sourceUrl
                    );                    
                    
                    $url = $href->getUrl();                                  
                    
                    if (is_string($url) && !in_array($url, $this->urls)) {
                        $this->urls[] = $url;
                    }
                }
            }
        }
        
        return $this->urls;       
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