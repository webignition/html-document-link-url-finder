<?php

namespace webignition\HtmlDocumentLinkUrlFinder\Tests;

class HasUrlsTest extends BaseTest {    

    public function testWithUrls() {
        $fixture = $this->getFixture('example01');
        $finder = $this->getFinder();
        $finder->setSourceContent($fixture);
        $finder->setSourceUrl('http://blog.example.com');
        
        $this->assertTrue($finder->hasUrls());
    }     
    
    public function testWithoutUrls() {
        $fixture = $this->getFixture('example03');
        $finder = $this->getFinder();
        $finder->setSourceContent($fixture);
        $finder->setSourceUrl('http://blog.example.com');
        
        $this->assertFalse($finder->hasUrls());
    }   
    
}