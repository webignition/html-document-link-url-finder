<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetAllUrls;

class EmptyContentTest extends GetAllUrlsTest {

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
        $this->assertEquals([], $this->getFinder()->getAllUrls());
    }
}