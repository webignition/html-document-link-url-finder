<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\NormalisedUrl\NormalisedUrl;
use webignition\WebResourceInterfaces\WebPageInterface;

class Configuration
{
    const CONFIG_KEY_SOURCE = 'source';
    const CONFIG_KEY_SOURCE_URL = 'source-url';
    const CONFIG_KEY_URL_SCOPE = 'url-scope';
    const CONFIG_KEY_ELEMENT_SCOPE = 'element-scope';
    const CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON = 'ignore-fragment-in-url-comparison';
    const CONFIG_KEY_ATTRIBUTE_SCOPE_NAME = 'attribute-scope-name';
    const CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE = 'attribute-scope-value';
    const CONFIG_KEY_IGNORE_EMPTY_HREF = 'ignore-empty-href';

    /**
     * @var bool
     */
    private $requiresReset = false;

    /**
     * @var WebPageInterface
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
     * @var bool
     */
    private $ignoreEmptyHref = false;

    /**
     * @param array $configurationValues
     */
    public function __construct(array $configurationValues = [])
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

        if (isset($configurationValues[self::CONFIG_KEY_IGNORE_EMPTY_HREF])) {
            $this->setIgnoreEmptyHref($configurationValues[self::CONFIG_KEY_IGNORE_EMPTY_HREF]);
        }
    }

    public function setIgnoreEmptyHref(bool $ignoreEmptyHref)
    {
        $this->ignoreEmptyHref = $ignoreEmptyHref;
        $this->requiresReset = true;
    }

    public function getIgnoreEmptyHref(): bool
    {
        return $this->ignoreEmptyHref;
    }

    public function setAttributeScope(string $name, string $value)
    {
        $this->attributeScopeName = $name;
        $this->attributeScopeValue = $value;
        $this->requiresReset = true;
    }

    public function getAttributeScopeName(): ?string
    {
        return $this->attributeScopeName;
    }

    public function getAttributeScopeValue(): ?string
    {
        return $this->attributeScopeValue;
    }

    public function setIgnoreFragmentInUrlComparison(bool $ignoreFragmentInUrlComparison)
    {
        $this->ignoreFragmentInUrlComparison = $ignoreFragmentInUrlComparison;
    }

    public function getIgnoreFragmentInUrlComparison(): bool
    {
        return $this->ignoreFragmentInUrlComparison;
    }

    public function setSource(WebPageInterface $webPage)
    {
        $this->source = $webPage;
        $this->requiresReset = true;
    }

    public function getSource(): ?WebPageInterface
    {
        return $this->source;
    }

    public function hasSourceContent(): bool
    {
        if (empty($this->source)) {
            return false;
        }

        return !empty(trim($this->source->getContent()));
    }

    public function requiresReset(): bool
    {
        return $this->requiresReset;
    }

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

    public function getUrlScope(): array
    {
        return $this->urlScope;
    }

    public function hasUrlScope(): bool
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

    public function getElementScope(): array
    {
        return $this->elementScope;
    }

    public function setSourceUrl(string $sourceUrl)
    {
        $this->sourceUrl = new NormalisedUrl($sourceUrl);
        $this->requiresReset = true;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }
}
