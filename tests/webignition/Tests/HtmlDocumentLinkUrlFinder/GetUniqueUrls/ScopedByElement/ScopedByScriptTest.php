<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByElement;

class ScopedByScriptTest extends ScopedByElementTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->setElementScope('script');
    }

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example04';
    }

    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com';
    }


    public function testGetAllElements() {
        $this->assertEquals(array(
            'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
            'http://example.com/vendor/twitter-bootstrap/bootstrap/js/bootstrap.js',
        ), $this->getFinder()->getUniqueUrls());
    }
    
}