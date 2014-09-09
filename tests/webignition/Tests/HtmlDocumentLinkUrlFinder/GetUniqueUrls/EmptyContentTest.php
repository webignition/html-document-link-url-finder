<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls;

class EmptyContentTest extends GetUniqueUrlsTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'empty';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://blog.example.com';
    }


    public function testEmptyContentReturnsEmptyUrlSet() {
        $this->assertEquals([], $this->getFinder()->getUniqueUrls());
    }
    
}