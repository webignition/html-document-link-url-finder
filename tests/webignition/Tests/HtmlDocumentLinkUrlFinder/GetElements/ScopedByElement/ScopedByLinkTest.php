<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByElement;

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
            '<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap.no-icons.min.css" rel="stylesheet">',
            '<link href="//netdna.bootstrapcdn.com/font-awesome/2.0/css/font-awesome.css" rel="stylesheet">',
            '<link href="/assets/css/main.css" rel="stylesheet">'
        ), $this->getFinder()->getElements());
    }
    
}