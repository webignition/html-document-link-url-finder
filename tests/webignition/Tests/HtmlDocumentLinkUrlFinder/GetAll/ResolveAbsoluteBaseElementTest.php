<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder\GetAll;

class ResolveAbsoluteBaseElementTest extends GetAllTest {

    /**
     * @return string
     */
    protected function getFixtureName() {
        return 'example07';
    }


    /**
     * @return string
     */
    protected function getSourceUrl() {
        return 'http://example.com/fiifii/';
    }
    
    public function testGetAll() {
        $this->assertEquals([
            array(
                'url' => 'http://base.example.com/foobar/foo/bar.html',
                'element' => '<a href="foo/bar.html">A</a>'
            ),
            array(
                'url' => 'http://base.example.com/foobar/foo/bar.html',
                'element' => '<a href="./foo/bar.html">B</a>'
            ),
            array(
                'url' => 'http://base.example.com/foo/bar.html',
                'element' => '<a href="../foo/bar.html">C</a>'
            ),
            array(
                'url' => 'http://base.example.com/foo/bar.html',
                'element' => '<a href="/foo/bar.html">D</a>'
            ),
            array(
                'url' => 'http://base.example.com/foobar/#identity',
                'element' => '<a href="#identity">E</a>'
            )
        ], $this->getFinder()->getAll());
    }
}