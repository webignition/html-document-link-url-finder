<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetAllUrls;

class ResolveRelativeBaseElementTest extends GetAllUrlsTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example08';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com/register/foo.php';
    }
    
    public function testGetAll() {
        $this->assertEquals([
            'http://example.com/',
            'http://example.com/one.html',
            'http://example.com/two',
            'http://example.com/foo/bar.html',
            'http://example.com/foo/bar.html'
        ], $this->getFinder()->getAllUrls());
    }
}