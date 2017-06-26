<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByUrl;

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
            'http://example.com/'
        ), $this->getFinder()->getUniqueUrls());
    }


    public function testGetScopedElementsAfterChangingScope() {
        $this->getFinder()->getConfiguration()->setUrlScope('http://www.example.com');

        $this->assertEquals(array(
            'http://www.example.com/'
        ), $this->getFinder()->getUniqueUrls());
    }

}