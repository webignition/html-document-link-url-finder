<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

class RelativeUrlResolutionTest extends BaseTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example06';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'https://gist.github.com/webignition/6809735';
    }
    
    public function testWithOutSourceTrailingSlash() {
        $this->assertEquals(array(
            'https://gist.github.com/webignition/foo/bar.html',
            'https://gist.github.com/webignition/foo/bar.html',
            'https://gist.github.com/foo/bar.html'
        ), $this->getFinder()->getAllUrls());
    }
    
}