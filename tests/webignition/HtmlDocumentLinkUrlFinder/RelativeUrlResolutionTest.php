<?php

namespace webignition\HtmlDocumentLinkUrlFinder\Tests;

class RelativeUrlResolutionTest extends BaseTest {    
    
    public function testWithOutSourceTrailingSlash() {
        $finder = $this->getFinder();
        $finder->setSourceContent($this->getFixture('example06'));
        $finder->setSourceUrl('https://gist.github.com/webignition/6809735');
        
        $this->assertEquals(array(
            'https://gist.github.com/webignition/foo/bar.html',
            'https://gist.github.com/webignition/foo/bar.html',
            'https://gist.github.com/foo/bar.html'
        ), $finder->getAllUrls());
    }
    
}