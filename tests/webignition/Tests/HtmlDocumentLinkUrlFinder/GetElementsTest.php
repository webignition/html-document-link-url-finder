<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

class GetElementsTest extends BaseTest {    
    
    public function testGetAll() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');

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
        ), $finder->getElements());
    } 
    
    public function testGetScopedToBlogExampleCom() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');
        $finder->setUrlScope('http://blog.example.com');
        
        $this->assertEquals(array(
            '<a href="relative-path">Relative Path</a>',
            '<a href="/root-relative-path">Root Relative Path</a>',
            '<a href="#fragment-only">Fragment Only</a>',
            '<a href="#fragment-only">Repeated Fragment Only (should be ignored)</a>',
            '<img src="/images/youtube.png">',
            '<a href="http://blog.example.com"><img src="/images/blog.png"></a>',
            '<img src="/images/blog.png">',
            '<img src="/images/twitter.png">'
        ), $finder->getElements());
    }    
    
    public function testGetScopedToExampleCom() {        
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example01'));
        $finder->setSourceUrl('http://blog.example.com');
        $finder->setUrlScope('http://example.com/');
        
        $this->assertEquals(array(
            '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>',
        ), $finder->getElements());
    }
    
    public function testSetScopeResetsUrlList() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example02'));
        $finder->setSourceUrl('http://example.com');
        
        $finder->setUrlScope('http://example.com/');        
        $this->assertEquals(array(
            '<a href="http://example.com/">Example no subdomain</a>'
        ), $finder->getElements());        
        
        $finder->setUrlScope('http://www.example.com/');        
        $this->assertEquals(array(
            '<a href="http://www.example.com/">Example www subdomain</a>'
        ), $finder->getElements());  
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
            '<a href="http://example.com/">Example no subdomain</a>',
            '<a href="http://www.example.com/">Example www subdomain</a>'
        ), $finder->getElements());
    }
    
    
    public function testGetAllTypes() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        
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
        ), $finder->getElements());        
    }
    
    public function testGetByContextA() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        $finder->setElementScope('a');
        
        $this->assertEquals(array(
            '<a href="relative-path">Relative Path</a>',
            '<a href="/root-relative-path">Root Relative Path</a>',
            '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>',
            '<a href="//another.example.com/protocol-relative-same-host">Protocol Relative Different Host</a>',
            '<a href="#fragment-only">Fragment Only</a>',
            '<a href="#fragment-only">Repeated Fragment Only (should be ignored)</a>',
            '<a href="http://www.youtube.com/example"><img src="/images/youtube.png"></a>',
            '<a href="http://blog.example.com"><img src="/images/blog.png"></a>',
            '<a href="http://twitter.com/example"><img src="/images/twitter.png"></a>'
        ), $finder->getElements());         
    }
    
    public function testGetByContextLink() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        $finder->setElementScope('link');
        
        $this->assertEquals(array(
            '<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.no-icons.min.css" rel="stylesheet">',
            '<link href="//netdna.bootstrapcdn.com/font-awesome/2.0/css/font-awesome.css" rel="stylesheet">',
            '<link href="/assets/css/main.css" rel="stylesheet">'
        ), $finder->getElements());         
    }    
    
    public function testGetByContextScript() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example04'));
        $finder->setSourceUrl('http://example.com');
        $finder->setElementScope('script');
        
        $this->assertEquals(array(
            '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>',
            '<script type="text/javascript" src="/vendor/twitter-bootstrap/bootstrap/js/bootstrap.js"></script>'
        ), $finder->getElements());
    }

    public function testWithEmptyContent() {
        $finder = $this->getFinder();
        $finder->setSourceContent("");
        $finder->setSourceUrl('http://example.com');

        $this->assertEquals([], $finder->getElements());
    }
    
}