<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use Mockery\Mock;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use webignition\HtmlDocumentLinkUrlFinder\Configuration;
use webignition\WebResource\Exception\InvalidContentTypeException;
use webignition\WebResource\WebPage\WebPage;
use webignition\WebResourceInterfaces\WebPageInterface;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
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
        array $configurationValues,
        string $expectedSource,
        string $expectedSourceUrl,
        array $expectedUrlScope,
        array $expectedElementScope,
        ?string $expectedAttributeScopeName,
        ?string $expectedAttributeScopeValue,
        bool $expectedIgnoreFragmentInUrlComparison,
        bool $expectedIgnoreEmptyHref
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

    public function createDataProvider(): array
    {
        $webPage = \Mockery::mock(WebPageInterface::class);

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
     * @param string|array $scope
     * @param array $expectedScope
     */
    public function testElementScope($scope, array $expectedScope)
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setElementScope($scope);
        $this->assertEquals($expectedScope, $this->configuration->getElementScope());

        $this->assertTrue($this->configuration->requiresReset());
    }

    public function elementScopeDataProvider(): array
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
    public function testSetSourceGetSource(WebPage $source, WebPage $expectedSource)
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setSource($source);
        $this->assertEquals($expectedSource, $this->configuration->getSource());

        $this->assertTrue($this->configuration->requiresReset());
    }

    /**
     * @return array
     *
     * @throws InvalidContentTypeException
     */
    public function sourceDataProvider(): array
    {
        $responseBody = \Mockery::mock(StreamInterface::class);
        $responseBody
            ->shouldReceive('__toString')
            ->andReturn('');

        /* @var ResponseInterface|Mock $response */
        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getHeaderLine')
            ->with('content-type')
            ->andReturn('text/html');

        $response
            ->shouldReceive('getBody')
            ->andReturn($responseBody);

        $webPage = WebPage::createFromResponse(\Mockery::mock(UriInterface::class), $response);

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
    public function testSourceUrl(string $sourceUrl, string $expectedSourceUrl)
    {
        $this->assertFalse($this->configuration->requiresReset());

        $this->configuration->setSourceUrl($sourceUrl);
        $this->assertEquals($expectedSourceUrl, $this->configuration->getSourceUrl());

        $this->assertTrue($this->configuration->requiresReset());
    }

    public function sourceUrlDataProvider(): array
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
     * @param string|array $scope
     * @param array $expectedScope
     */
    public function testUrlScope($scope, array $expectedScope)
    {
        $this->assertFalse($this->configuration->requiresReset());
        $this->assertFalse($this->configuration->hasUrlScope());

        $this->configuration->setUrlScope($scope);
        $this->assertEquals($expectedScope, $this->configuration->getUrlScope());
        $this->assertTrue($this->configuration->hasUrlScope());

        $this->assertTrue($this->configuration->requiresReset());
    }

    public function urlScopeDataProvider(): array
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
     * @param WebPage|null $source
     * @param bool $expectedHasSourceContent
     */
    public function testHasSourceContent($source, bool $expectedHasSourceContent)
    {
        if (!empty($source)) {
            $this->configuration->setSource($source);
        }

        $this->assertEquals($expectedHasSourceContent, $this->configuration->hasSourceContent());
    }

    public function hasSourceContentDataProvider(): array
    {
        $emptyWebPage = \Mockery::mock(WebPage::class);
        $emptyWebPage
            ->shouldReceive('getContent')
            ->andReturn('');

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
                'source' => $emptyWebPage,
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
