<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\Link;
use webignition\HtmlDocumentLinkUrlFinder\LinkCollection;

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
            '<a href="/3" id="3">'
        );

        $this->links = [
            new Link('http://example.com/1', $domDocument->getElementById('1')),
            new Link('http://example.com/1', $domDocument->getElementById('1.1')),
            new Link('http://example.com/2', $domDocument->getElementById('2')),
            new Link('http://example.com/2#foo', $domDocument->getElementById('2.1')),
            new Link('http://example.com/3', $domDocument->getElementById('3')),
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

    public function testGetUrls()
    {
        $this->assertEquals(
            [
                'http://example.com/1',
                'http://example.com/1',
                'http://example.com/2',
                'http://example.com/2#foo',
                'http://example.com/3',
            ],
            $this->linkCollection->getUrls()
        );
    }

    public function testGetUniqueUrls()
    {
        $this->assertEquals(
            [
                'http://example.com/1',
                'http://example.com/2',
                'http://example.com/2#foo',
                'http://example.com/3',
            ],
            $this->linkCollection->getUniqueUrls()
        );
    }

    public function testGetUniqueUrlsIgnoringFragment()
    {
        $this->assertEquals(
            [
                'http://example.com/1',
                'http://example.com/2',
                'http://example.com/3',
            ],
            $this->linkCollection->getUniqueUrls(true)
        );
    }
}
