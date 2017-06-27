<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebResource\WebPage\WebPage;

class Configuration
{
    const CONFIG_KEY_SOURCE = 'source';
    const CONFIG_KEY_SOURCE_URL = 'source-url';
    const CONFIG_KEY_URL_SCOPE = 'url-scope';
    const CONFIG_KEY_ELEMENT_SCOPE = 'element-scope';
    const CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON = 'ignore-fragment-in-url-comparison';
    const CONFIG_KEY_ATTRIBUTE_SCOPE_NAME = 'attribute-scope-name';
    const CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE = 'attribute-scope-value';

    /**
     * @var bool
     */
    private $requiresReset = false;

    /**
     * @var WebPage
     */
    private $source = null;

    /**
     * @var string
     */
    private $sourceUrl = null;

    /**
     * @var array
     */
    private $urlScope = [];

    /**
     * @var array
     */
    private $elementScope = [];

    /**
     * @var string
     */
    private $attributeScopeName = null;

    /**
     * @var string
     */
    private $attributeScopeValue = null;

    /**
     * @var bool
     */
    private $ignoreFragmentInUrlComparison = false;

    /**
     * @param array $configurationValues
     */
    public function __construct($configurationValues = [])
    {
        if (isset($configurationValues[self::CONFIG_KEY_SOURCE])) {
            $this->setSource($configurationValues[self::CONFIG_KEY_SOURCE]);
        }

        if (isset($configurationValues[self::CONFIG_KEY_SOURCE_URL])) {
            $this->setSourceUrl($configurationValues[self::CONFIG_KEY_SOURCE_URL]);
        }

        if (isset($configurationValues[self::CONFIG_KEY_URL_SCOPE])) {
            $this->setUrlScope($configurationValues[self::CONFIG_KEY_URL_SCOPE]);
        }

        if (isset($configurationValues[self::CONFIG_KEY_ELEMENT_SCOPE])) {
            $this->setElementScope($configurationValues[self::CONFIG_KEY_ELEMENT_SCOPE]);
        }

        $hasAttributeScopeName = isset($configurationValues[self::CONFIG_KEY_ATTRIBUTE_SCOPE_NAME]);
        $hasAttributeScopeValue = isset($configurationValues[self::CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE]);

        if ($hasAttributeScopeName && $hasAttributeScopeValue) {
            $this->setAttributeScope(
                $configurationValues[self::CONFIG_KEY_ATTRIBUTE_SCOPE_NAME],
                $configurationValues[self::CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE]
            );
        }

        if (isset($configurationValues[self::CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON])) {
            $this->setIgnoreFragmentInUrlComparison(
                $configurationValues[self::CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON]
            );
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setAttributeScope($name, $value)
    {
        $this->attributeScopeName = $name;
        $this->attributeScopeValue = $value;
        $this->requiresReset = true;
    }

    /**
     * @return string
     */
    public function getAttributeScopeName()
    {
        return $this->attributeScopeName;
    }

    /**
     * @return string
     */
    public function getAttributeScopeValue()
    {
        return $this->attributeScopeValue;
    }

    /**
     * @param bool $ignoreFragmentInUrlComparison
     */
    public function setIgnoreFragmentInUrlComparison($ignoreFragmentInUrlComparison)
    {
        $this->ignoreFragmentInUrlComparison = $ignoreFragmentInUrlComparison;
    }

    /**
     * @return bool
     */
    public function getIgnoreFragmentInUrlComparison()
    {
        return $this->ignoreFragmentInUrlComparison;
    }

    /**
     * @param WebPage $webPage
     */
    public function setSource(WebPage $webPage)
    {
        $this->source = $webPage;
        $this->requiresReset = true;
    }

    /**
     * @return WebPage
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return bool
     */
    public function hasSourceContent()
    {
        if (empty($this->source)) {
            return false;
        }

        return !empty(trim($this->source->getContent()));
    }

    /**
     * @return bool
     */
    public function requiresReset()
    {
        return $this->requiresReset;
    }

    /**
     * @return Configuration
     */
    public function clearReset()
    {
        $this->requiresReset = false;
    }

    /**
     * @param string|array $scope
     */
    public function setUrlScope($scope)
    {
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
    }

    /**
     * @return array
     */
    public function getUrlScope()
    {
        return $this->urlScope;
    }

    /**
     * @return bool
     */
    public function hasUrlScope()
    {
        return !empty($this->urlScope);
    }

    /**
     * @param string|array $scope
     */
    public function setElementScope($scope)
    {
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
    }

    /**
     * @return array
     */
    public function getElementScope()
    {
        return $this->elementScope;
    }


    /**
     * @param $sourceUrl
     */
    public function setSourceUrl($sourceUrl)
    {
        $this->sourceUrl = new NormalisedUrl($sourceUrl);
        $this->requiresReset = true;
    }

    /**
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }
}
