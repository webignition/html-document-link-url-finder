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

        $this->links = [
            new Link('http://example.com/1', '<a href="/1">'),
            new Link('http://example.com/1', '<a href="/1" class="again">'),
            new Link('http://example.com/2', '<a href="/2">>'),
            new Link('http://example.com/2#foo', '<a href="/2#foo">>'),
            new Link('http://example.com/3', '<a href="/3">>'),
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
