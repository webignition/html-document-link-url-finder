<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\HasUrls;

class WithoutUrlsTest extends HasUrlsTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example03';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com';
    }


    public function testHasUrls() {
        $this->assertFalse($this->getFinder()->hasUrls());
    }
    
}