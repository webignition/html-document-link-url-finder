<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

class IgnoreFragmentInUrlComparisonTest extends BaseTest {
    
    public function testTest() {
        $finder = $this->getFinder();
        $finder->getConfiguration()->enableIgnoreFragmentInUrlComparison();
        $finder->setSourceContent($this->getFixture('example09'));
        $finder->setSourceUrl('http://example.com/');

        $this->assertEquals(['http://example.com/'], $finder->getUniqueUrls());
    } 
    

    
}