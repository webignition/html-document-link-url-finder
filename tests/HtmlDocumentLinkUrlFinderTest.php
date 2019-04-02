<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use webignition\HtmlDocumentLinkUrlFinder\Configuration;
use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;
use webignition\HtmlDocumentLinkUrlFinder\LinkCollection;
use webignition\WebResource\Exception\InvalidContentTypeException;
use webignition\WebResource\WebPage\WebPage;

class HtmlDocumentLinkUrlFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HtmlDocumentLinkUrlFinder
     */
    private $htmlDocumentLinkUrlFinder;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->htmlDocumentLinkUrlFinder = new HtmlDocumentLinkUrlFinder();
    }

    /**
     * @dataProvider getLinkCollectionDataProvider
     */
    public function testGetLinkCollection(Configuration $configuration, array $expectedLinkCollectionData)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);

        $linkCollection = $this->htmlDocumentLinkUrlFinder->getLinkCollection();

        $this->assertInstanceOf(LinkCollection::class, $linkCollection);
        $this->assertCount(count($expectedLinkCollectionData), $linkCollection);

        foreach ($linkCollection as $index => $link) {
            $expectedLinkData = $expectedLinkCollectionData[$index];

            $this->assertEquals($expectedLinkData['url'], $link->getUri());
            $this->assertEquals($expectedLinkData['element'], $link->getElementAsString());
        }
    }

    public function getLinkCollectionDataProvider(): array
    {
        return [
            'no source content' => [
                'configuration' => new Configuration(),
                'expectedLinkCollectionData' => [],
            ],
            'empty source content' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage('', 'utf8'),
                ]),
                'expectedLinkCollectionData' => [],
            ],
            'empty body' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('empty-body'),
                        'utf-8'
                    ),
                ]),
                'expectedLinkCollectionData' => [],
            ],
            'single anchor lacking href attribute' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('missing-url'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedLinkCollectionData' => [],
            ],
            'single anchor; leading null bytes' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('leading-null-bytes'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://example.com/foo',
                        'element' => '<a href="/foo">Foo</a>',
                    ],
                ],
            ],
            'default' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://cdn.example.com/foo.css',
                        'element' => '<link href="//cdn.example.com/foo.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/assets/css/main.css',
                        'element' => '<link href="/assets/css/main.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<link href="" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://cdn.example.com/foo.js',
                        'element' => '<script type="text/javascript" src="//cdn.example.com/foo.js"></script>',
                    ],
                    [
                        'url' => 'http://example.com/assets/vendor/foo.js',
                        'element' => '<script type="text/javascript" src="/assets/vendor/foo.js"></script>',
                    ],
                    [
                        'url' => 'http://example.com/relative-path',
                        'element' => '<a href="relative-path">Relative Path</a>',
                    ],
                    [
                        'url' => 'http://example.com/root-relative-path',
                        'element' => '<a href="/root-relative-path">Root Relative Path</a>',
                    ],
                    [
                        'url' => 'http://example.com/protocol-relative-same-host',
                        'element' =>
                            '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>',
                    ],
                    [
                        'url' => 'http://another.example.com/protocol-relative-same-host',
                        'element' =>
                            '<a href="//another.example.com/protocol-relative-same-host">'
                            .'Protocol Relative Different Host'
                            .'</a>',
                    ],
                    [
                        'url' => 'http://example.com/#fragment-only',
                        'element' => '<a href="#fragment-only">Fragment Only</a>',
                    ],
                    [
                        'url' => 'http://example.com/#fragment-only',
                        'element' => '<a href="#fragment-only">Repeated Fragment Only</a>',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<a href="http://example.com/">Example no subdomain</a>',
                    ],
                    [
                        'url' => 'http://www.example.com/',
                        'element' => '<a href="http://www.example.com/">Example www subdomain</a>',
                    ],
                    [
                        'url' => 'http://example.com/foo/bar.html',
                        'element' => '<a href="./foo/bar.html">Path resolution example</a>',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<a href="#">Empty fragment only</a>',
                    ],
                    [
                        'url' => 'http://www.youtube.com/example',
                        'element' => '<a href="http://www.youtube.com/example"><img src="/images/youtube.png"></a>',
                    ],
                    [
                        'url' => 'http://example.com/images/youtube.png',
                        'element' => '<img src="/images/youtube.png">',
                    ],
                ],
            ],
            'element scope link' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_ELEMENT_SCOPE => 'link',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://cdn.example.com/foo.css',
                        'element' => '<link href="//cdn.example.com/foo.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/assets/css/main.css',
                        'element' => '<link href="/assets/css/main.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<link href="" rel="stylesheet">',
                    ],
                ],
            ],
            'element scope link, no source content type' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        null
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_ELEMENT_SCOPE => 'link',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://cdn.example.com/foo.css',
                        'element' => '<link href="//cdn.example.com/foo.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/assets/css/main.css',
                        'element' => '<link href="/assets/css/main.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<link href="" rel="stylesheet">',
                    ],
                ],
            ],
            'element scope link, script' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_ELEMENT_SCOPE => [
                        'link',
                        'script',
                    ],
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://cdn.example.com/foo.css',
                        'element' => '<link href="//cdn.example.com/foo.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/assets/css/main.css',
                        'element' => '<link href="/assets/css/main.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<link href="" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://cdn.example.com/foo.js',
                        'element' => '<script type="text/javascript" src="//cdn.example.com/foo.js"></script>',
                    ],
                    [
                        'url' => 'http://example.com/assets/vendor/foo.js',
                        'element' => '<script type="text/javascript" src="/assets/vendor/foo.js"></script>',
                    ],
                ],
            ],
            'url scope http://example.com' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_URL_SCOPE => 'http://example.com',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://example.com/assets/css/main.css',
                        'element' => '<link href="/assets/css/main.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<link href="" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/assets/vendor/foo.js',
                        'element' => '<script type="text/javascript" src="/assets/vendor/foo.js"></script>',
                    ],
                    [
                        'url' => 'http://example.com/relative-path',
                        'element' => '<a href="relative-path">Relative Path</a>',
                    ],
                    [
                        'url' => 'http://example.com/root-relative-path',
                        'element' => '<a href="/root-relative-path">Root Relative Path</a>',
                    ],
                    [
                        'url' => 'http://example.com/protocol-relative-same-host',
                        'element' =>
                            '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>',
                    ],
                    [
                        'url' => 'http://example.com/#fragment-only',
                        'element' => '<a href="#fragment-only">Fragment Only</a>',
                    ],
                    [
                        'url' => 'http://example.com/#fragment-only',
                        'element' => '<a href="#fragment-only">Repeated Fragment Only</a>',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<a href="http://example.com/">Example no subdomain</a>',
                    ],
                    [
                        'url' => 'http://example.com/foo/bar.html',
                        'element' => '<a href="./foo/bar.html">Path resolution example</a>',
                    ],
                    [
                        'url' => 'http://example.com/',
                        'element' => '<a href="#">Empty fragment only</a>',
                    ],
                    [
                        'url' => 'http://example.com/images/youtube.png',
                        'element' => '<img src="/images/youtube.png">',
                    ],
                ],
            ],
            'url scope http://cdn.example.com, http://www.example.com' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_URL_SCOPE => [
                        'http://cdn.example.com',
                        'http://www.example.com',
                    ],
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://cdn.example.com/foo.css',
                        'element' => '<link href="//cdn.example.com/foo.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://cdn.example.com/foo.js',
                        'element' => '<script type="text/javascript" src="//cdn.example.com/foo.js"></script>',
                    ],
                    [
                        'url' => 'http://www.example.com/',
                        'element' => '<a href="http://www.example.com/">Example www subdomain</a>',
                    ],
                ],
            ],
            'base element' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('base-element'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/foo',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://base.example.com/foobar/foo/bar.html',
                        'element' => '<a href="foo/bar.html">A</a>',
                    ],
                    [
                        'url' => 'http://base.example.com/foobar/foo/bar.html',
                        'element' => '<a href="./foo/bar.html">B</a>',
                    ],
                    [
                        'url' => 'http://base.example.com/foo/bar.html',
                        'element' => '<a href="../foo/bar.html">C</a>',
                    ],
                    [
                        'url' => 'http://base.example.com/foo/bar.html',
                        'element' => '<a href="/foo/bar.html">D</a>',
                    ],
                    [
                        'url' => 'http://base.example.com/foobar/#identity',
                        'element' => '<a href="#identity">E</a>',
                    ],
                ],
            ],
            'empty base element' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('empty-base-element'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/foo',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://example.com/foo/bar.html',
                        'element' => '<a href="foo/bar.html">A</a>',
                    ],
                ],
            ],
            'badly-formed markup with JS concatenated URLs' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('badly-formed-js-urls'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://example.com/',
                        'element' => '<a href="http://example.com/">Example no subdomain</a>',
                    ],
                    [
                        'url' => 'http://example.com/foo.js',
                        'element' => '<script type="text/javascript" src="/foo.js"></script>',
                    ],
                ],
            ],
            'ignore link rel=dns-prefetch' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('link-rel-equals-dns-prefetch'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedLinkCollectionData' => [
                    [
                        'url' => 'http://example.com/main.css',
                        'element' => '<link href="main.css" rel="stylesheet">',
                    ],
                    [
                        'url' => 'http://example.com/foo',
                        'element' => '<a href="http://example.com/foo">A</a>',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getAllUrlsDataProvider
     *
     * @param Configuration $configuration
     * @param array $expectedResult
     */
    public function testGetAllUrls(Configuration $configuration, array $expectedResult)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);
        $this->htmlDocumentLinkUrlFinder->getConfiguration()->setElementScope(
            $configuration->getElementScope()
        );

        $result = $this->htmlDocumentLinkUrlFinder->getAllUrls();

        $this->assertEquals($expectedResult, $result);
    }

    public function getAllUrlsDataProvider(): array
    {
        return [
            'element scope link, attribute scope rel=stylesheet, ignore empty href' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_ELEMENT_SCOPE => 'link',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_NAME => 'rel',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE => 'stylesheet',
                    Configuration::CONFIG_KEY_IGNORE_EMPTY_HREF => true,
                ]),
                'expectedResult' => [
                    'http://cdn.example.com/foo.css',
                    'http://example.com/assets/css/main.css',
                ],
            ],
        ];
    }

    public function testGetConfiguration()
    {
        $this->assertInstanceOf(Configuration::class, $this->htmlDocumentLinkUrlFinder->getConfiguration());
    }

    /**
     * @param string $content
     * @param string $characterSet
     * @param UriInterface|null $uri
     *
     * @return WebPage
     */
    private function createWebPage(string $content, ?string $characterSet, ?UriInterface $uri = null)
    {
        $contentTypeString = 'text/html';

        if ($characterSet) {
            $contentTypeString .= '; charset=' . $characterSet;
        }

        $responseBody = \Mockery::mock(StreamInterface::class);
        $responseBody
            ->shouldReceive('__toString')
            ->andReturn($content);

        $response = \Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getHeaderLine')
            ->with(WebPage::HEADER_CONTENT_TYPE)
            ->andReturn($contentTypeString);

        $response
            ->shouldReceive('getBody')
            ->andReturn($responseBody);

        $webPage = null;

        if (empty($uri)) {
            $uri = $this->createUri();
        }

        /* @var WebPage $webPage */
        try {
            $webPage = WebPage::createFromResponse(
                $uri,
                $response
            );
        } catch (InvalidContentTypeException $e) {
        }

        return $webPage;
    }

    private function createUri(?string $uriString = '')
    {
        $uri = \Mockery::mock(UriInterface::class);
        $uri
            ->shouldReceive('__toString')
            ->andReturn($uriString);

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        \Mockery::close();
    }

    private function loadHtmlDocumentFixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/fixtures/html-documents/' . $name . '.html');
    }
}
