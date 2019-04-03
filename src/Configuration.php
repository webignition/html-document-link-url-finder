<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\Uri\Normalizer;
use webignition\Uri\Uri;
use webignition\WebResourceInterfaces\WebPageInterface;

class Configuration
{
    const CONFIG_KEY_SOURCE = 'source';
    const CONFIG_KEY_SOURCE_URL = 'source-url';
    const CONFIG_KEY_ELEMENT_SCOPE = 'element-scope';
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

    public function setSourceUrl(string $sourceUrl)
    {
        $this->sourceUrl = (string) Normalizer::normalize(new Uri($sourceUrl));
        $this->requiresReset = true;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }
}
