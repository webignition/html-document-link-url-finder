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
     */
    public function testCreate(
        array $configurationValues,
        string $expectedSource,
        string $expectedSourceUrl
    ) {
        $configuration = new Configuration($configurationValues);

        $this->assertEquals($expectedSource, $configuration->getSource());
        $this->assertEquals($expectedSourceUrl, $configuration->getSourceUrl());
    }

    public function createDataProvider(): array
    {
        $webPage = \Mockery::mock(WebPageInterface::class);

        return [
            'default' => [
                'configurationValues' => [],
                'expectedSource' => '',
                'expectedSourceUrl' => '',
            ],
            'non-default' => [
                'configurationValues' => [
                    Configuration::CONFIG_KEY_SOURCE => $webPage,
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ],
                'expectedSource' => $webPage,
                'expectedSourceUrl' => 'http://example.com/',
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
}
