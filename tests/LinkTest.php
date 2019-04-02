<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\Link;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $url = 'http://example.com/';
        $element = '<a href="http://example.com/">Example</a>';

        $link = new Link($url, $element);

        $this->assertSame($url, $link->getUrl());
        $this->assertSame($element, $link->getElement());
    }
}
