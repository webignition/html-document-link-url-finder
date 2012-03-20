<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

/**
 * 
 * @package webignition\HtmlDocumentLinkUrlFinder
 *
 */
class DocumentHref extends \webignition\HtmlDocumentLinkUrlFinder\Url {
    
    
    /**
     *
     * @var string
     */
    private $documentUrl = null;
    
    
    
    /**
     *
     * @param string $href 
     */
    public function __construct($url, $documentUrl) {
        parent::__construct($url);
        $this->documentUrl = new \webignition\HtmlDocumentLinkUrlFinder\Url($documentUrl);
    }
    
    
    /**
     *
     * @return string
     */
    public function getUrl() {
        return $this->getScheme().'://'.$this->getCredentialsString().$this->getHost().$this->getPath().$this->getQueryString();

    }
    
    /**
     *
     * @return string
     */    
    public function getScheme() {
        return (parent::getScheme() == '') ? $this->documentUrl->getScheme() : parent::getScheme();
    }
    
    /**
     *
     * @return string
     */    
    public function getHost() {
        return (parent::getHost() == '') ? $this->documentUrl->getHost() : parent::getHost();
    }
    
    /**
     *
     * @return string
     */    
    public function getUsername() {
        return (parent::getUsername() == '') ? $this->documentUrl->getUsername() : parent::getUsername();
    }
    
    /**
     *
     * @return string
     */    
    public function getPassword() {
        return (parent::getPassword() == '') ? $this->documentUrl->getPassword() : parent::getPassword();
    }
    
    /**
     *
     * @return string
     */    
    public function getPath() {
        return (parent::getPath() == '') ? $this->documentUrl->getPath() : parent::getPath();
    }
    
    /**
     *
     * @return string
     */    
    public function getFragment() {
        return (parent::getFragment() == '') ? $this->documentUrl->getFragment() : parent::getFragment();
    }
}