<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByUrl;

class ScopedToBlogExampleComTest extends ScopedByUrlTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->setUrlScope('http://blog.example.com');
    }

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example01';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://blog.example.com';
    }


    public function testGetScopedElements() {
        $this->assertEquals(array(
            '<a href="relative-path">Relative Path</a>',
            '<a href="/root-relative-path">Root Relative Path</a>',
            '<a href="#fragment-only">Fragment Only</a>',
            '<a href="#fragment-only">Repeated Fragment Only (should be ignored)</a>',
            '<img src="/images/youtube.png">',
            '<a href="http://blog.example.com"><img src="/images/blog.png"></a>',
            '<img src="/images/blog.png">',
            '<img src="/images/twitter.png">'
        ), $this->getFinder()->getElements());
    }

}