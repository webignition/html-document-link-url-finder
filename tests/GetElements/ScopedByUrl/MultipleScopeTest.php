<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetElements\ScopedByUrl;

class MultipleScopeTest extends ScopedByUrlTest {

    public function setUp() {
        parent::setUp();
        $this->getFinder()->getConfiguration()->setUrlScope([
            'http://example.com/',
            'http://www.example.com/'
        ]);
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
        return 'http://blog.example.com';
    }


    public function testGetScopedElements() {
        $this->assertEquals(array(
            '<a href="http://example.com/">Example no subdomain</a>',
            '<a href="http://www.example.com/">Example www subdomain</a>'
        ), $this->getFinder()->getElements());
    }

}