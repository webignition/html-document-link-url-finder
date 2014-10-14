<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetAllUrls;

class WithCharacterSetTest extends GetAllUrlsTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example11';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com/';
    }

    public function testGetAll() {
        $this->assertEquals([
            'http://example.com/tags/foo–bar/'
        ],$this->getFinder()->getAllUrls());
    }
}