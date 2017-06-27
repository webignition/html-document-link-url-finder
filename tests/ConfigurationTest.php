<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\Configuration;
use webignition\WebResource\WebPage\WebPage;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->configuration = new Configuration();
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param array $configurationValues
     * @param string $expectedSource
     * @param string $expectedSourceUrl
     * @param array $expectedUrlScope
     * @param array $expectedElementScope
     * @param string $expectedAttributeScopeName
     * @param string $expectedAttributeScopeValue
     * @param bool $expectedIgnoreFragmentInUrlComparison
     * @param bool $expectedIgnoreEmptyHref
     */
    public function testCreate(
        $configurationValues,
        $expectedSource,
        $expectedSourceUrl,
        $expectedUrlScope,
        $expectedElementScope,
        $expectedAttributeScopeName,
        $expectedAttributeScopeValue,
        $expectedIgnoreFragmentInUrlComparison,
        $expectedIgnoreEmptyHref
    ) {
        $configuration = new Configuration($configurationValues);

        $this->assertEquals($expectedSource, $configuration->getSource());
        $this->assertEquals($expectedSourceUrl, $configuration->getSourceUrl());
        $this->assertEquals($expectedUrlScope, $configuration->getUrlScope());
        $this->assertEquals($expectedElementScope, $configuration->getElementScope());
        $this->assertEquals($expectedAttributeScopeName, $configuration->getAttributeScopeName());
        $this->assertEquals($expectedAttributeScopeValue, $configuration->getAttributeScopeValue());
        $this->assertEquals($expectedIgnoreFragmentInUrlComparison, $configuration->getIgnoreFragmentInUrlComparison());
        $this->assertEquals($expectedIgnoreEmptyHref, $configuration->getIgnoreEmptyHref());
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        $webPage = \Mockery::mock(WebPage::class);

        return [
            'default' => [
                'configurationValues' => [],
                'expectedSource' => '',
                'expectedSourceUrl' => '',
                'expectedUrlScope' => [],
                'expectedElementScope' => [],
                'expectedAttributeScopeName' => null,
                'expectedAttributeScopeValue' => null,
                'expectedIgnoreFragmentInUrlComparison' => false,
                'expectedIgnoreEmptyHref' => false,
            ],
            'non-default' => [
                'configurationValues' => [
                    Configuration::CONFIG_KEY_SOURCE => $webPage,
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_URL_SCOPE => 'http://example.com/',
                    Configuration::CONFIG_KEY_ELEMENT_SCOPE => 'a',
                    Configuration::CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON => true,
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_NAME => 'name',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE => 'value',
                    Configuration::CONFIG_KEY_IGNORE_EMPTY_HREF => true,
                ],
                'expectedSource' => $webPage,
                'expectedSourceUrl' => 'http://example.com/',
                'expectedUrlScope' => [
                    'http://example.com/',
                ],
                'expectedElementScope' => [
                    'a',
                ],
                'expectedAttributeScopeName' => 'name',
                'expectedAttributeScopeValue' => 'value',
                'expectedIgnoreFragmentInUrlComparison' => true,
                'expectedIgnoreEmptyHref' => true,
            ],
        ];
    }

    /**
     * @dataProvider elementScopeDataProvider
     *
     * @param string $scope
     * @param string $expectedScope
     */
    public function testElementScope($scope, $expectedScope)
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setElementScope($scope);
        $this->assertEquals($expectedScope, $this->configuration->getElementScope());

        $this->assertTrue($this->configuration->requiresReset());
    }

    /**
     * @return array
     */
    public function elementScopeDataProvider()
    {
        return [
            'a' => [
                'scope' => 'a',
                'expectedScope' => [
                    'a'
                ],
            ],
            'link' => [
                'scope' => 'link',
                'expectedScope' => [
                    'link'
                ],
            ],
            'a, link' => [
                'scope' => [
                    'a',
                    'link',
                ],
                'expectedScope' => [
                    'a',
                    'link',
                ],
            ],
            'A, LINK' => [
                'scope' => [
                    'A',
                    'LINK',
                ],
                'expectedScope' => [
                    'a',
                    'link',
                ],
            ],
        ];
    }

    /**
     * @dataProvider sourceDataProvider
     *
     * @param WebPage $source
     * @param WebPage $expectedSource
     */
    public function testSource(WebPage $source, WebPage $expectedSource)
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setSource($source);
        $this->assertEquals($expectedSource, $this->configuration->getSource());

        $this->assertTrue($this->configuration->requiresReset());
    }

    /**
     * @return array
     */
    public function sourceDataProvider()
    {
        $webPage = new WebPage();

        return [
            'default' => [
                'source' => $webPage,
                'expectedSource' => $webPage,
            ],
        ];
    }

    /**
     * @dataProvider sourceUrlDataProvider
     *
     * @param string $sourceUrl
     * @param string $expectedSourceUrl
     */
    public function testSourceUrl($sourceUrl, $expectedSourceUrl)
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setSourceUrl($sourceUrl);
        $this->assertEquals($expectedSourceUrl, $this->configuration->getSourceUrl());

        $this->assertTrue($this->configuration->requiresReset());
    }

    /**
     * @return array
     */
    public function sourceUrlDataProvider()
    {
        return [
            'default' => [
                'sourceUrl' => 'http://example.com/',
                'expectedSourceUrl' => 'http://example.com/',
            ],
        ];
    }

    /**
     * @dataProvider urlScopeDataProvider
     *
     * @param string $scope
     * @param array $expectedScope
     */
    public function testUrlScope($scope, $expectedScope)
    {
        $this->assertFalse($this->configuration->requiresReset());
        $this->assertFalse($this->configuration->hasUrlScope());

        $this->configuration->setUrlScope($scope);
        $this->assertEquals($expectedScope, $this->configuration->getUrlScope());
        $this->assertTrue($this->configuration->hasUrlScope());

        $this->assertTrue($this->configuration->requiresReset());
    }

    /**
     * @return array
     */
    public function urlScopeDataProvider()
    {
        return [
            'string' => [
                'scope' => 'http://example.com/',
                'expectedScope' => [
                    'http://example.com/',
                ],
            ],
            'array' => [
                'scope' => [
                    'http://example.com/',
                    'http://example.org/',
                ],
                'expectedScope' => [
                    'http://example.com/',
                    'http://example.org/',
                ],
            ],
        ];
    }

    /**
     * @dataProvider hasSourceContentDataProvider
     *
     * @param WebPage $source
     * @param bool $expectedHasSourceContent
     */
    public function testHasSourceContent($source, $expectedHasSourceContent)
    {
        if (!empty($source)) {
            $this->configuration->setSource($source);
        }

        $this->assertEquals($expectedHasSourceContent, $this->configuration->hasSourceContent());
    }

    /**
     * @return array
     */
    public function hasSourceContentDataProvider()
    {
        $webPage = \Mockery::mock(WebPage::class);
        $webPage
            ->shouldReceive('getContent')
            ->andReturn('foo');

        return [
            'no source' => [
                'source' => null,
                'expectedHasSourceContent' => false,
            ],
            'empty source' => [
                'source' => new WebPage(),
                'expectedHasSourceContent' => false,
            ],
            'non-empty source' => [
                'source' => $webPage,
                'expectedHasSourceContent' => true,
            ],
        ];
    }

    public function testIgnoreFragmentInUrlComparison()
    {
        $this->configuration->setIgnoreFragmentInUrlComparison(false);
        $this->assertFalse($this->configuration->getIgnoreFragmentInUrlComparison());

        $this->configuration->setIgnoreFragmentInUrlComparison(true);
        $this->assertTrue($this->configuration->getIgnoreFragmentInUrlComparison());
    }

    public function testClearReset()
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setElementScope('foo');
        $this->assertTrue($this->configuration->requiresReset());

        $this->configuration->clearReset();
        $this->assertFalse($this->configuration->requiresReset());
    }
}
