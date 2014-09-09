<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetUniqueUrls\ScopedByElement;

class ScopedByLinkTest extends ScopedByElementTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->setElementScope('link');
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
            'http://netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.no-icons.min.css',
            'http://netdna.bootstrapcdn.com/font-awesome/2.0/css/font-awesome.css',
            'http://example.com/assets/css/main.css',
        ), $this->getFinder()->getUniqueUrls());
    }
    
}