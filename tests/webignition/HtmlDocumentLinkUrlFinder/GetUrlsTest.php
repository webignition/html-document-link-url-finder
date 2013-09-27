<?php

namespace webignition\HtmlDocumentLinkUrlFinder\Tests;

class GetUrlsTest extends BaseTest {    
    
    public function testGetAll() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');

        $this->assertEquals(array(
            'http://blog.example.com/relative-path',
            'http://blog.example.com/root-relative-path',
            'http://example.com/protocol-relative-same-host',
            'http://another.example.com/protocol-relative-same-host',
            'http://blog.example.com/#fragment-only',
            'http://www.youtube.com/example',
            'http://blog.example.com/images/youtube.png',
            'http://blog.example.com/',
            'http://blog.example.com/images/blog.png',
            'http://twitter.com/example',
            'http://blog.example.com/images/twitter.png',
        ), $finder->getUrls());
    } 
    
    public function testGetScopedToBlogExampleCom() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');
        $finder->setUrlScope('http://blog.example.com');
        
        $this->assertEquals(array(
            'http://blog.example.com/relative-path',
            'http://blog.example.com/root-relative-path',
            'http://blog.example.com/#fragment-only',
            'http://blog.example.com/images/youtube.png',
            'http://blog.example.com/',
            'http://blog.example.com/images/blog.png',
            'http://blog.example.com/images/twitter.png',
        ), $finder->getUrls());
    }    
    
    public function testGetScopedToExampleCom() {        
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');
        $finder->setUrlScope('http://example.com/');
        
        $this->assertEquals(array(
            'http://example.com/protocol-relative-same-host',
        ), $finder->getUrls());
    }
    
    public function testSetScopeResetsUrlList() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example02'));
        $finder->setSourceUrl('http://example.com');
        
        $finder->setUrlScope('http://example.com/');        
        $this->assertEquals(array(
            'http://example.com/'
        ), $finder->getUrls());        
        
        $finder->setUrlScope('http://www.example.com/');        
        $this->assertEquals(array(
            'http://www.example.com/'
        ), $finder->getUrls());  
    }
    
    
    public function testMultipleScope() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example02'));
        $finder->setSourceUrl('http://example.com');
        
        $finder->setUrlScope(array(
            'http://example.com/',
            'http://www.example.com/'
        ));        
        $this->assertEquals(array(
            'http://example.com/',
            'http://www.example.com/'
        ), $finder->getUrls());
    }
    
    
    public function testGetAllTypes() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        
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
            'http://example.com/#fragment-only',
            'http://www.youtube.com/example',
            'http://example.com/images/youtube.png',
            'http://blog.example.com/',
            'http://example.com/images/blog.png',
            'http://twitter.com/example',
            'http://example.com/images/twitter.png'
        ), $finder->getUrls());        
    }
    
    public function testGetByContextA() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        $finder->setElementScope('a');
        
        $this->assertEquals(array(
            'http://example.com/relative-path',
            'http://example.com/root-relative-path',
            'http://example.com/protocol-relative-same-host',
            'http://another.example.com/protocol-relative-same-host',
            'http://example.com/#fragment-only',
            'http://www.youtube.com/example',
            'http://blog.example.com/',
            'http://twitter.com/example',
        ), $finder->getUrls());         
    }
    
    public function testGetByContextLink() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        $finder->setElementScope('link');
        
        $this->assertEquals(array(
            'http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.no-icons.min.css',
            'http://netdna.bootstrapcdn.com/font-awesome/2.0/css/font-awesome.css',
            'http://example.com/assets/css/main.css',
        ), $finder->getUrls());         
    }    
    
    public function testGetByContextScript() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        $finder->setElementScope('script');
        
        $this->assertEquals(array(
            'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
            'http://example.com/vendor/twitter-bootstrap/bootstrap/js/bootstrap.js',
        ), $finder->getUrls());
    }
    
}