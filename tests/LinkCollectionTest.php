<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\Link;
use webignition\HtmlDocumentLinkUrlFinder\LinkCollection;
use webignition\Uri\ScopeComparer;
use webignition\Uri\Uri;

class LinkCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Link[]
     */
    private $links = [];

    /**
     * @var LinkCollection
     */
    private $linkCollection;

    protected function setUp()
    {
        parent::setUp();

        $domDocument = new \DOMDocument();
        $domDocument->loadHTML(
            '<a href="/1" id="1">' .
            '<a href="/1" id="1.1">' .
            '<a href="/2" id="2">' .
            '<a href="/2#foo" id="2.1">' .
            '<link href="/3" id="3">'
        );

        $this->links = [
            new Link(new Uri('http://foo.example.com/1'), $domDocument->getElementById('1')),
            new Link(new Uri('http://foo.example.com/1'), $domDocument->getElementById('1.1')),
            new Link(new Uri('http://example.com/2'), $domDocument->getElementById('2')),
            new Link(new Uri('http://example.com/2#foo'), $domDocument->getElementById('2.1')),
            new Link(new Uri('http://bar.example.com/3'), $domDocument->getElementById('3')),
        ];

        $this->linkCollection = new LinkCollection($this->links);
    }

    public function testIterator()
    {
        foreach ($this->linkCollection as $linkIndex => $link) {
            $this->assertInstanceOf(Link::class, $link);
            $this->assertSame($this->links[$linkIndex], $link);
        }
    }

    public function testGetUris()
    {
        $this->assertEquals(
            [
                'http://foo.example.com/1',
                'http://foo.example.com/1',
                'http://example.com/2',
                'http://example.com/2#foo',
                'http://bar.example.com/3',
            ],
            $this->linkCollection->getUris()
        );
    }

    /**
     * @dataProvider getUniqueUrisDataProvider
     */
    public function testGetUniqueUris(bool $ignoreFragment, array $expectedUris)
    {
        $this->assertEquals($expectedUris, $this->linkCollection->getUniqueUris($ignoreFragment));
    }

    public function getUniqueUrisDataProvider(): array
    {
        return [
            'ignoreFragment false' => [
                'ignoreFragment' => false,
                'expectedUris' => [
                    'http://foo.example.com/1',
                    'http://example.com/2',
                    'http://example.com/2#foo',
                    'http://bar.example.com/3',
                ],
            ],
            'ignoreFragment true' => [
                'ignoreFragment' => true,
                'expectedUris' => [
                    'http://foo.example.com/1',
                    'http://example.com/2',
                    'http://bar.example.com/3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider filterByElementNameDataProvider
     */
    public function testFilterByElementName(string $elementName, array $expectedUris)
    {
        $filteredLinkCollection = $this->linkCollection->filterByElementName($elementName);

        $this->assertInstanceOf(LinkCollection::class, $filteredLinkCollection);
        $this->assertNotSame($this->linkCollection, $filteredLinkCollection);
        $this->assertEquals($expectedUris, $filteredLinkCollection->getUris());
    }

    public function filterByElementNameDataProvider(): array
    {
        return [
            'a' => [
                'elementName' => 'a',
                'expectedUris' => [
                    'http://foo.example.com/1',
                    'http://foo.example.com/1',
                    'http://example.com/2',
                    'http://example.com/2#foo',
                ],
            ],
            'link' => [
                'elementName' => 'link',
                'expectedUris' => [
                    'http://bar.example.com/3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider filterByAttributeDataProvider
     */
    public function testFilterByAttribute(string $name, string $value, array $expectedUris)
    {
        $filteredLinkCollection = $this->linkCollection->filterByAttribute($name, $value);

        $this->assertInstanceOf(LinkCollection::class, $filteredLinkCollection);
        $this->assertNotSame($this->linkCollection, $filteredLinkCollection);
        $this->assertEquals($expectedUris, $filteredLinkCollection->getUris());
    }

    public function filterByAttributeDataProvider(): array
    {
        return [
            'href="/1"' => [
                'name' => 'href',
                'value' => '/1',
                'expectedUris' => [
                    'http://foo.example.com/1',
                    'http://foo.example.com/1',
                ],
            ],
            'id="3"' => [
                'name' => 'id',
                'value' => '3',
                'expectedUris' => [
                    'http://bar.example.com/3',
                ],
            ],
        ];
    }

    /**
     * @dataProvider filterByUriScopeDataProvider
     */
    public function testFilterByUriScope(callable $scopeComparerCreator, array $scope, array $expectedUris)
    {
        $scopeComparer = $scopeComparerCreator();

        $filteredLinkCollection = $this->linkCollection->filterByUriScope(
            $scopeComparer,
            $scope
        );

        $this->assertInstanceOf(LinkCollection::class, $filteredLinkCollection);
        $this->assertNotSame($this->linkCollection, $filteredLinkCollection);

        $this->assertEquals(
            $expectedUris,
            $filteredLinkCollection->getUris()
        );
    }

    public function filterByUriScopeDataProvider(): array
    {
        return [
            'default scope comparer, http://foo.example.com/' => [
                'scopeComparerCreator' => function () {
                    return new ScopeComparer();
                },
                'scope' => [
                    new Uri('http://foo.example.com/'),
                ],
                'expectedUris' => [
                    'http://foo.example.com/1',
                    'http://foo.example.com/1',
                ],
            ],
            'default scope comparer, https://foo.example.com/' => [
                'scopeComparerCreator' => function () {
                    return new ScopeComparer();
                },
                'scope' => [
                    new Uri('https://foo.example.com/'),
                ],
                'expectedUris' => [],
            ],
            'https/http equivalent scope comparer, https://foo.example.com/' => [
                'scopeComparerCreator' => function () {
                    $scopeComparer = new ScopeComparer();
                    $scopeComparer->addEquivalentSchemes(['http', 'https']);

                    return $scopeComparer;
                },
                'scope' => [
                    new Uri('https://foo.example.com/'),
                ],
                'expectedUris' => [
                    'http://foo.example.com/1',
                    'http://foo.example.com/1',
                ],
            ],
        ];
    }
}
