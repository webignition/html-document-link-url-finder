<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByUrl;

class UnscopedTest extends ScopedByUrlTest {

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


    public function testGetAllElements() {
        $this->assertEquals(array(
            '<a href="relative-path">Relative Path</a>',
            '<a href="/root-relative-path">Root Relative Path</a>',
            '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>',
            '<a href="//another.example.com/protocol-relative-same-host">Protocol Relative Different Host</a>',
            '<a href="#fragment-only">Fragment Only</a>',
            '<a href="#fragment-only">Repeated Fragment Only (should be ignored)</a>',
            '<a href="http://www.youtube.com/example"><img src="/images/youtube.png"></a>',
            '<img src="/images/youtube.png">',
            '<a href="http://blog.example.com"><img src="/images/blog.png"></a>',
            '<img src="/images/blog.png">',
            '<a href="http://twitter.com/example"><img src="/images/twitter.png"></a>',
            '<img src="/images/twitter.png">'
        ), $this->getFinder()->getElements());
    }

}