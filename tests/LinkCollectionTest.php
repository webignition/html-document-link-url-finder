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

    public function testGetUniqueUris()
    {
        $this->assertEquals(
            [
                'http://foo.example.com/1',
                'http://example.com/2',
                'http://example.com/2#foo',
                'http://bar.example.com/3',
            ],
            $this->linkCollection->getUniqueUris()
        );
    }

    public function testGetUniqueUrisIgnoringFragment()
    {
        $this->assertEquals(
            [
                'http://foo.example.com/1',
                'http://example.com/2',
                'http://bar.example.com/3',
            ],
            $this->linkCollection->getUniqueUris(true)
        );
    }

    public function testFilterByElementName()
    {
        $aElementFilteredLinkCollection = $this->linkCollection->filterByElementName('a');

        $this->assertInstanceOf(LinkCollection::class, $aElementFilteredLinkCollection);
        $this->assertNotSame($this->linkCollection, $aElementFilteredLinkCollection);
        $this->assertCount(4, $aElementFilteredLinkCollection);

        $linkElementFilteredLinkCollection = $this->linkCollection->filterByElementName('link');

        $this->assertInstanceOf(LinkCollection::class, $linkElementFilteredLinkCollection);
        $this->assertNotSame($this->linkCollection, $linkElementFilteredLinkCollection);
        $this->assertCount(1, $linkElementFilteredLinkCollection);
    }

    public function testFilterByAttribute()
    {
        $filteredLinkCollection = $this->linkCollection->filterByAttribute('href', "/1");

        $this->assertInstanceOf(LinkCollection::class, $filteredLinkCollection);
        $this->assertNotSame($this->linkCollection, $filteredLinkCollection);
        $this->assertCount(2, $filteredLinkCollection);
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
