<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebResource\WebPage\WebPage;

class Configuration {

    /**
     * @var bool
     */
    private $requiresReset = false;


    /**
     *
     * @var WebPage
     */
    private $source = null;


    /**
     *
     * @var string
     */
    private $sourceUrl = null;


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
     * @var bool
     */
    private $ignoreFragmentInUrlComparison = false;


    /**
     * @return Configuration
     */
    public function enableIgnoreFragmentInUrlComparison() {
        $this->ignoreFragmentInUrlComparison = true;
        return $this;
    }


    /**
     * @return Configuration
     */
    public function disableIgnoreFragmentInUrlComparison() {
        $this->ignoreFragmentInUrlComparison = false;
        return $this;
    }


    /**
     * @return bool
     */
    public function ignoreFragmentInUrlComparison() {
        return $this->ignoreFragmentInUrlComparison;
    }


    /**
     * @param WebPage $webPage
     * @return Configuration
     */
    public function setSource(WebPage $webPage) {
        $this->source = $webPage;
        $this->requiresReset = true;
        return $this;
    }


    /**
     * @return WebPage
     */
    public function getSource() {
        return $this->source;
    }


    /**
     * @return bool
     */
    public function hasSourceContent() {
        if (!$this->source instanceof WebPage) {
            return false;
        }

        return trim($this->source->getContent() != '');
    }


    /**
     * @return bool
     */
    public function requiresReset() {
        return $this->requiresReset;
    }


    /**
     * @return Configuration
     */
    public function clearReset() {
        $this->requiresReset = false;
        return $this;
    }


    /**
     * @param string|array $scope
     * @return Configuration
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

        $this->requiresReset = true;

        return $this;
    }


    /**
     *
     * @return array
     */
    public function getUrlScope() {
        return $this->urlScope;
    }


    /**
     * @return bool
     */
    public function hasUrlScope() {
        return !is_null($this->urlScope);
    }


    /**
     * @param string|array $scope
     * @return Configuration
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

        $this->requiresReset = true;

        return $this;
    }


    /**
     *
     * @return array
     */
    public function getElementScope() {
        return $this->elementScope;
    }


    /**
     * @param $sourceUrl
     * @return Configuration
     */
    public function setSourceUrl($sourceUrl) {
        $this->sourceUrl = new NormalisedUrl($sourceUrl);
        $this->requiresReset = true;
        return $this;
    }


    /**
     *
     * @return string
     */
    public function getSourceUrl() {
        return $this->sourceUrl;
    }

}