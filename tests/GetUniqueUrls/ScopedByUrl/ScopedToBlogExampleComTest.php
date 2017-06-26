<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByUrl;

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
            'http://blog.example.com/relative-path',
            'http://blog.example.com/root-relative-path',
            'http://blog.example.com/#fragment-only',
            'http://blog.example.com/images/youtube.png',
            'http://blog.example.com/',
            'http://blog.example.com/images/blog.png',
            'http://blog.example.com/images/twitter.png',
        ), $this->getFinder()->getUniqueUrls());
    }

}