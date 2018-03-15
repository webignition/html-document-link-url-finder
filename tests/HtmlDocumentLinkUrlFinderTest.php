<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use Mockery\MockInterface;
use webignition\HtmlDocumentLinkUrlFinder\Configuration;
use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;
use webignition\WebResource\WebPage\WebPage;

class HtmlDocumentLinkUrlFinderTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider getAllDataProvider
     *
     * @param Configuration $configuration
     * @param array $expectedResult
     */
    public function testGetAll(Configuration $configuration, $expectedResult)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);
        $this->htmlDocumentLinkUrlFinder->getConfiguration()->setElementScope(
            $configuration->getElementScope()
        );

        $result = $this->htmlDocumentLinkUrlFinder->getAll();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getAllDataProvider()
    {
        return [
            'no source content' => [
                'configuration' => new Configuration(),
                'expectedResult' => [],
            ],
            'empty source content' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage('', 'utf8'),
                ]),
                'expectedResult' => [],
            ],
            'empty body' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('empty-body'),
                        'utf-8'
                    ),
                ]),
                'expectedResult' => [],
            ],
            'default' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [
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
                        'url' => 'http://example.com/#',
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
                'expectedResult' => [
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
                'expectedResult' => [
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
                'expectedResult' => [
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
                'expectedResult' => [
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
                        'url' => 'http://example.com/#',
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
                'expectedResult' => [
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
                'expectedResult' => [
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
                'expectedResult' => [
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
                'expectedResult' => [
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
        ];
    }

    /**
     * @dataProvider getAllUrlsDataProvider
     *
     * @param Configuration $configuration
     * @param array $expectedResult
     */
    public function testGetAllUrls(Configuration $configuration, $expectedResult)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);
        $this->htmlDocumentLinkUrlFinder->getConfiguration()->setElementScope(
            $configuration->getElementScope()
        );

        $result = $this->htmlDocumentLinkUrlFinder->getAllUrls();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getAllUrlsDataProvider()
    {
        return [
            'no source content' => [
                'configuration' => new Configuration(),
                'expectedResult' => [],
            ],
            'missing url' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('missing-url'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [],
            ],
            'default' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [
                    'http://cdn.example.com/foo.css',
                    'http://example.com/assets/css/main.css',
                    'http://example.com/',
                    'http://cdn.example.com/foo.js',
                    'http://example.com/assets/vendor/foo.js',
                    'http://example.com/relative-path',
                    'http://example.com/root-relative-path',
                    'http://example.com/protocol-relative-same-host',
                    'http://another.example.com/protocol-relative-same-host',
                    'http://example.com/#fragment-only',
                    'http://example.com/#fragment-only',
                    'http://example.com/',
                    'http://www.example.com/',
                    'http://example.com/foo/bar.html',
                    'http://example.com/#',
                    'http://www.youtube.com/example',
                    'http://example.com/images/youtube.png',
                ],
            ],
            'leading null bytes' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('leading-null-bytes'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [
                    'http://example.com/foo',
                ],
            ],
            'attribute scope rel=stylesheet' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_NAME => 'rel',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE => 'stylesheet',
                ]),
                'expectedResult' => [
                    'http://cdn.example.com/foo.css',
                    'http://example.com/assets/css/main.css',
                    'http://example.com/',
                ],
            ],
            'element scope link, attribute scope rel=stylesheet' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_ELEMENT_SCOPE => 'link',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_NAME => 'rel',
                    Configuration::CONFIG_KEY_ATTRIBUTE_SCOPE_VALUE => 'stylesheet',
                ]),
                'expectedResult' => [
                    'http://cdn.example.com/foo.css',
                    'http://example.com/assets/css/main.css',
                    'http://example.com/',
                ],
            ],
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
            'badly-formed markup with JS concatenated URLs' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('badly-formed-js-urls'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [
                    'http://example.com/',
                    'http://example.com/foo.js',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getUniqueUrlsDataProvider
     *
     * @param Configuration $configuration
     * @param array $expectedResult
     */
    public function testGetUniqueUrls(Configuration $configuration, $expectedResult)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);
        $this->htmlDocumentLinkUrlFinder->getConfiguration()->setElementScope(
            $configuration->getElementScope()
        );

        $result = $this->htmlDocumentLinkUrlFinder->getUniqueUrls();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getUniqueUrlsDataProvider()
    {
        return [
            'no source content' => [
                'configuration' => new Configuration(),
                'expectedResult' => [],
            ],
            'default' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('uniqueness'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [
                    'http://example.com/foo',
                    'http://example.com/#fragment-only',
                ],
            ],
            'ignore fragment in url comparison' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('uniqueness'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON => true,
                ]),
                'expectedResult' => [
                    'http://example.com/foo',
                    'http://example.com/',
                ],
            ],
            'badly-formed markup with JS concatenated URLs' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('badly-formed-js-urls'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                    Configuration::CONFIG_KEY_IGNORE_FRAGMENT_IN_URL_COMPARISON => true,
                ]),
                'expectedResult' => [
                    'http://example.com/',
                    'http://example.com/foo.js',
                ],
            ],
        ];
    }

    /**
     * @dataProvider hasUrlsDataProvider
     *
     * @param Configuration $configuration
     * @param bool $expectedHasUrls
     */
    public function testHasUniqueUrls(Configuration $configuration, $expectedHasUrls)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);
        $this->htmlDocumentLinkUrlFinder->getConfiguration()->setElementScope(
            $configuration->getElementScope()
        );

        $this->assertEquals($expectedHasUrls, $this->htmlDocumentLinkUrlFinder->hasUrls());
    }

    /**
     * @return array
     */
    public function hasUrlsDataProvider()
    {
        return [
            'no source content' => [
                'configuration' => new Configuration(),
                'expectedHasUrls' => false,
            ],
            'empty source content' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage('', 'utf8'),
                ]),
                'expectedHasUrls' => false,
            ],
            'empty body' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('empty-body'),
                        'utf-8'
                    ),
                ]),
                'expectedHasUrls' => false,
            ],
            'has urls' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('uniqueness'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedHasUrls' => true,
            ],
        ];
    }

    /**
     * @dataProvider getElementsDataProvider
     *
     * @param Configuration $configuration
     * @param array $expectedResult
     */
    public function testGetElements(Configuration $configuration, $expectedResult)
    {
        $this->htmlDocumentLinkUrlFinder->setConfiguration($configuration);
        $this->htmlDocumentLinkUrlFinder->getConfiguration()->setElementScope(
            $configuration->getElementScope()
        );

        $result = $this->htmlDocumentLinkUrlFinder->getElements();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getElementsDataProvider()
    {
        return [
            'no source content' => [
                'configuration' => new Configuration(),
                'expectedResult' => [],
            ],
            'default' => [
                'configuration' => new Configuration([
                    Configuration::CONFIG_KEY_SOURCE => $this->createWebPage(
                        $this->loadHtmlDocumentFixture('example01'),
                        'utf-8'
                    ),
                    Configuration::CONFIG_KEY_SOURCE_URL => 'http://example.com/',
                ]),
                'expectedResult' => [
                    '<link href="//cdn.example.com/foo.css" rel="stylesheet">',
                    '<link href="/assets/css/main.css" rel="stylesheet">',
                    '<link href="" rel="stylesheet">',
                    '<script type="text/javascript" src="//cdn.example.com/foo.js"></script>',
                    '<script type="text/javascript" src="/assets/vendor/foo.js"></script>',
                    '<a href="relative-path">Relative Path</a>',
                    '<a href="/root-relative-path">Root Relative Path</a>',
                    '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>',
                    '<a href="//another.example.com/protocol-relative-same-host">'
                    .'Protocol Relative Different Host'
                    .'</a>',
                    '<a href="#fragment-only">Fragment Only</a>',
                    '<a href="#fragment-only">Repeated Fragment Only</a>',
                    '<a href="http://example.com/">Example no subdomain</a>',
                    '<a href="http://www.example.com/">Example www subdomain</a>',
                    '<a href="./foo/bar.html">Path resolution example</a>',
                    '<a href="#">Empty fragment only</a>',
                    '<a href="http://www.youtube.com/example"><img src="/images/youtube.png"></a>',
                    '<img src="/images/youtube.png">',
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
                'expectedResult' => [
                    '<a href="http://example.com/">Example no subdomain</a>',
                    '<script type="text/javascript" src="/foo.js"></script>',
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
     * @return MockInterface|WebPage
     */
    private function createWebPage($content, $characterSet)
    {
        $webPage = \Mockery::mock(WebPage::class);
        $webPage
            ->shouldReceive('getContent')
            ->andReturn($content);

        $webPage
            ->shouldReceive('getCharacterSet')
            ->andReturn($characterSet);

        return $webPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        \Mockery::close();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function loadHtmlDocumentFixture($name)
    {
        return file_get_contents(__DIR__ . '/fixtures/html-documents/' . $name . '.html');
    }
}
