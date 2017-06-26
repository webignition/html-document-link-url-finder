<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByUrl;

use webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\GetUniqueUrlsTest;

class IgnoreFragmentTest extends GetUniqueUrlsTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->enableIgnoreFragmentInUrlComparison();
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


    public function testGetUniqueUrls() {
        $this->assertEquals(array(
            'http://blog.example.com/relative-path',
            'http://blog.example.com/root-relative-path',
            'http://example.com/protocol-relative-same-host',
            'http://another.example.com/protocol-relative-same-host',
            'http://blog.example.com/',
            'http://www.youtube.com/example',
            'http://blog.example.com/images/youtube.png',
            'http://blog.example.com/images/blog.png',
            'http://twitter.com/example',
            'http://blog.example.com/images/twitter.png',
        ), $this->getFinder()->getUniqueUrls());
    }

}