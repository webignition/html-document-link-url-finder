<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

class GetAllTest extends BaseTest {    
    
    public function testGetAll() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');
        
        $this->assertEquals(array(
            array(
                'url' => 'http://blog.example.com/relative-path',
                'element' => '<a href="relative-path">Relative Path</a>'
            ),
            array(
                'url' => 'http://blog.example.com/root-relative-path',
                'element' => '<a href="/root-relative-path">Root Relative Path</a>'
            ),
            array(
                'url' => 'http://example.com/protocol-relative-same-host',
                'element' => '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>'
            ),
            array(
                'url' => 'http://another.example.com/protocol-relative-same-host',
                'element' => '<a href="//another.example.com/protocol-relative-same-host">Protocol Relative Different Host</a>'
            ),
            array(
                'url' => 'http://blog.example.com/#fragment-only',
                'element' => '<a href="#fragment-only">Fragment Only</a>'
            ),
            array(
                'url' => 'http://blog.example.com/#fragment-only',
                'element' => '<a href="#fragment-only">Repeated Fragment Only (should be ignored)</a>'
            ),
            array(
                'url' => 'http://www.youtube.com/example',
                'element' => '<a href="http://www.youtube.com/example"><img src="/images/youtube.png"></a>'
            ),
            array(
                'url' => 'http://blog.example.com/images/youtube.png',
                'element' => '<img src="/images/youtube.png">'
            ),
            array(
                'url' => 'http://blog.example.com/',
                'element' => '<a href="http://blog.example.com"><img src="/images/blog.png"></a>'
            ),
            array(
                'url' => 'http://blog.example.com/images/blog.png',
                'element' => '<img src="/images/blog.png">'
            ),
            array(
                'url' => 'http://twitter.com/example',
                'element' => '<a href="http://twitter.com/example"><img src="/images/twitter.png"></a>'
            ),
            array(
                'url' => 'http://blog.example.com/images/twitter.png',
                'element' => '<img src="/images/twitter.png">'
            ),            
        ), $finder->getAll());
    }   
    
    
    public function testFixUndefinedOffsetMappingUrlToElement() {        
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example05'));
        $finder->setSourceUrl('http://blog.simplytestable.com');

        $this->assertEquals(count($finder->getAllUrls()), count($finder->getElements()));
    }


    public function testWithEmptyContent() {
        $finder = $this->getFinder();
        $finder->setSourceContent("");
        $finder->setSourceUrl('http://example.com');

        $this->assertEquals([], $finder->getAll());
        $this->assertEquals([], $finder->getAllUrls());
    }
    
}