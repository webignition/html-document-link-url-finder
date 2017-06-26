<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByElement;

class UnscopedTest extends ScopedByElementTest {

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


    public function testGetAllElements() {
        $this->assertEquals(array(
            '<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.no-icons.min.css" rel="stylesheet">',
            '<link href="//netdna.bootstrapcdn.com/font-awesome/2.0/css/font-awesome.css" rel="stylesheet">',
            '<link href="/assets/css/main.css" rel="stylesheet">',
            '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>',
            '<script type="text/javascript" src="/vendor/twitter-bootstrap/bootstrap/js/bootstrap.js"></script>',
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