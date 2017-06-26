<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByElement;

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
            '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>',
            '<script type="text/javascript" src="/vendor/twitter-bootstrap/bootstrap/js/bootstrap.js"></script>'
        ), $this->getFinder()->getElements());
    }
    
}