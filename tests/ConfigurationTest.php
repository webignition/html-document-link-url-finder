<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use GuzzleHttp\Message\MessageFactory;
use GuzzleHttp\Message\ResponseInterface;
use webignition\HtmlDocumentLinkUrlFinder\Configuration;
use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;
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
        /* @var ResponseInterface $httpResponse */
        $httpResponse = \Mockery::mock(ResponseInterface::class);
        $httpResponse
            ->shouldReceive('getBody')
            ->andReturn('foo');

        $httpResponse
            ->shouldReceive('getHeader')
            ->with('content-type')
            ->andReturn('text/html');

        $webPage = new WebPage();
        $webPage->setHttpResponse($httpResponse);

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
