<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\Link;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $elementSource = '<a href="http://example.com/" id="example">Example</a>';

        $domDocument = new \DOMDocument();
        $domDocument->loadHTML($elementSource);

        $url = 'http://example.com/';
        $element = $domDocument->getElementById('example');

        $link = new Link($url, $element);

        $this->assertSame($url, $link->getUrl());
        $this->assertSame($element, $link->getElement());
        $this->assertEquals($elementSource, $link->getElementAsString());
    }
}
