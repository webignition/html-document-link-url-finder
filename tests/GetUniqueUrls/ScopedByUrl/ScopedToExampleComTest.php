<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByUrl;

class ScopedToExampleComTest extends ScopedByUrlTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->setUrlScope('http://example.com');
    }

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
        return 'http://blog.example.com';
    }


    public function testGetScopedElements() {
        $this->assertEquals(array(
            'http://example.com/protocol-relative-same-host'
        ), $this->getFinder()->getUniqueUrls());
    }

}