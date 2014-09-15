<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByUrl;

use webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\GetUniqueUrlsTest;

class IgnoreEmptyFragmentTest extends GetUniqueUrlsTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->enableIgnoreFragmentInUrlComparison();
    }

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example10';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com/path';
    }


    public function testGetUniqueUrls() {
        $this->assertEquals([
            'http://example.com/path'
        ], $this->getFinder()->getUniqueUrls());
    }

}