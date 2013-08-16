<?php

namespace webignition\HtmlDocumentLinkUrlFinder\Tests;

class GetUrlsTest extends BaseTest {    
    
    public function testGetAll() {
        $fixture = $this->getFixture('example01');
        $finder = $this->getFinder();
        $finder->setSourceContent($fixture);
        $finder->setSourceUrl('http://blog.example.com');
        
        $this->assertEquals(array(
            'http://blog.example.com/relative-path',
            'http://blog.example.com/root-relative-path',
            'http://example.com/protocol-relative-same-host',
            'http://another.example.com/protocol-relative-same-host',
            'http://blog.example.com/#fragment-only',
            'http://www.youtube.com/example',
            'http://blog.example.com/',
            'http://twitter.com/example'
        ), $finder->getUrls());
    } 
    
    public function testGetScopedToBlogExampleCom() {
        $fixture = $this->getFixture('example01');
        $finder = $this->getFinder();
        $finder->setSourceContent($fixture);
        $finder->setSourceUrl('http://blog.example.com');
        $finder->setScope('http://blog.example.com');
        
        $this->assertEquals(array(
            'http://blog.example.com/relative-path',
            'http://blog.example.com/root-relative-path',
            'http://blog.example.com/#fragment-only',
            'http://blog.example.com/'
        ), $finder->getUrls());
    }    
    
    public function testGetScopedToExampleCom() {        
        $fixture = $this->getFixture('example01');
        $finder = $this->getFinder();
        $finder->setSourceContent($fixture);
        $finder->setSourceUrl('http://blog.example.com');
        $finder->setScope('http://example.com/');
        
        $this->assertEquals(array(
            'http://example.com/protocol-relative-same-host',
        ), $finder->getUrls());
    }
    
    public function testSetScopeResetsUrlList() {
        $fixture = $this->getFixture('example02');
        $finder = $this->getFinder();
        $finder->setSourceContent($fixture);
        $finder->setSourceUrl('http://example.com');
        
        $finder->setScope('http://example.com/');        
        $this->assertEquals(array(
            'http://example.com/'
        ), $finder->getUrls());        
        
        $finder->setScope('http://www.example.com/');        
        $this->assertEquals(array(
            'http://www.example.com/'
        ), $finder->getUrls());  
    }
    
}