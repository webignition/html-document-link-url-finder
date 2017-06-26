<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\HasUrls;

class WithUrlsTest extends HasUrlsTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example01';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com';
    }


    public function testHasUrls() {
        $this->assertTrue($this->getFinder()->hasUrls());
    }
    
}