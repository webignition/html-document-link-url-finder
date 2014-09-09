<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetAllUrls;

class UndefinedOffsetMappingUrlToElementTestWithResultCollectionTest extends GetAllUrlsTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example05';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://blog.simplytestable.com';
    }

    public function testFixUndefinedOffsetMappingUrlToElement() {
        $this->assertEquals(count($this->getFinder()->getAllUrls()), count($this->getFinder()->getElements()));
    }
}