<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByElement;

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
        return 'example04';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com';
    }


    public function testGetUniqueUrls() {
        $this->assertEquals(array(
            'http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.no-icons.min.css',
            'http://netdna.bootstrapcdn.com/font-awesome/2.0/css/font-awesome.css',
            'http://example.com/assets/css/main.css',
            'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
            'http://example.com/vendor/twitter-bootstrap/bootstrap/js/bootstrap.js',
            'http://example.com/relative-path',
            'http://example.com/root-relative-path',
            'http://example.com/protocol-relative-same-host',
            'http://another.example.com/protocol-relative-same-host',
            'http://example.com/',
            'http://www.youtube.com/example',
            'http://example.com/images/youtube.png',
            'http://blog.example.com/',
            'http://example.com/images/blog.png',
            'http://twitter.com/example',
            'http://example.com/images/twitter.png'
        ), $this->getFinder()->getUniqueUrls());
    }

}