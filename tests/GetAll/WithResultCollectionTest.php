<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetAll;

class WithResultCollectionTest extends GetAllTest {

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
    
    public function testGetAll() {
        $this->assertEquals([
            [
                'url' => 'http://blog.example.com/relative-path',
                'element' => '<a href="relative-path">Relative Path</a>'
            ],
            [
                'url' => 'http://blog.example.com/root-relative-path',
                'element' => '<a href="/root-relative-path">Root Relative Path</a>'
            ],
            [
                'url' => 'http://example.com/protocol-relative-same-host',
                'element' => '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>'
            ],
            [
                'url' => 'http://another.example.com/protocol-relative-same-host',
                'element' => '<a href="//another.example.com/protocol-relative-same-host">Protocol Relative Different Host</a>'
            ],
            [
                'url' => 'http://blog.example.com/#fragment-only',
                'element' => '<a href="#fragment-only">Fragment Only</a>'
            ],
            [
                'url' => 'http://blog.example.com/#fragment-only',
                'element' => '<a href="#fragment-only">Repeated Fragment Only (should be ignored)</a>'
            ],
            [
                'url' => 'http://www.youtube.com/example',
                'element' => '<a href="http://www.youtube.com/example"><img src="/images/youtube.png"></a>'
            ],
            [
                'url' => 'http://blog.example.com/images/youtube.png',
                'element' => '<img src="/images/youtube.png">'
            ],
            [
                'url' => 'http://blog.example.com/',
                'element' => '<a href="http://blog.example.com"><img src="/images/blog.png"></a>'
            ],
            [
                'url' => 'http://blog.example.com/images/blog.png',
                'element' => '<img src="/images/blog.png">'
            ],
            [
                'url' => 'http://twitter.com/example',
                'element' => '<a href="http://twitter.com/example"><img src="/images/twitter.png"></a>'
            ],
            [
                'url' => 'http://blog.example.com/images/twitter.png',
                'element' => '<img src="/images/twitter.png">'
            ],
        ], $this->getFinder()->getAll());
    }
}