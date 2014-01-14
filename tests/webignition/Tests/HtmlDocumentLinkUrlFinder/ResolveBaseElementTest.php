<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\Tests\HtmlDocumentLinkUrlFinder\BaseTest;

class ResolveBaseElementTest extends BaseTest {    
    
    public function testGetAll() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example07'));
        $finder->setSourceUrl('http://example.com/fiifii/');
        
        $this->assertEquals(array(
            array(
                'url' => 'http://base.example.com/foobar/foo/bar.html',
                'element' => '<a href="foo/bar.html">A</a>'
            ),
            array(
                'url' => 'http://base.example.com/foobar/foo/bar.html',
                'element' => '<a href="./foo/bar.html">B</a>'                
            ),
            array(
                'url' => 'http://base.example.com/foo/bar.html',
                'element' => '<a href="../foo/bar.html">C</a>'                
            ),
            array(
                'url' => 'http://base.example.com/foo/bar.html',
                'element' => '<a href="/foo/bar.html">D</a>'                
            ),
            array(
                'url' => 'http://base.example.com/foobar/#identity',
                'element' => '<a href="#identity">E</a>'                
            )            
        ), $finder->getAll()); 
    }
    
    
    public function testRelativeBaseUrl() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example08'));
        $finder->setSourceUrl('http://example.com/register/foo.php');
        
        $this->assertEquals(array(
            'http://example.com/',
            'http://example.com/one.html',
            'http://example.com/two',
            'http://example.com/foo/bar.html',
            'http://example.com/foo/bar.html'
            
        ), $finder->getAllUrls());
    }
    
}