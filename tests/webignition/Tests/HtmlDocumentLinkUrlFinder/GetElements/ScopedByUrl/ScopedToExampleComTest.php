<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByUrl;

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
            '<a href="//example.com/protocol-relative-same-host">Protocol Relative Same Host</a>'
        ), $this->getFinder()->getElements());
    }

}