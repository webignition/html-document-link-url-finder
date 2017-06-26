<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByUrl;

class SettingScopeResetsUrlListTest extends ScopedByUrlTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->setUrlScope('http://example.com');
    }

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example02';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com';
    }


    public function testGetScopedElementsWithInitialScope() {
        $this->assertEquals(array(
            '<a href="http://example.com/">Example no subdomain</a>'
        ), $this->getFinder()->getElements());
    }


    public function testGetScopedElementsAfterChangingScope() {
        $this->getFinder()->getConfiguration()->setUrlScope('http://www.example.com');

        $this->assertEquals(array(
            '<a href="http://www.example.com/">Example www subdomain</a>'
        ), $this->getFinder()->getElements());
    }

}