<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\Link;
use webignition\Uri\Uri;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $elementSource = '<a href="http://example.com/" id="example">Example</a>';

        $domDocument = new \DOMDocument();
        $domDocument->loadHTML($elementSource);

        $uri = new Uri('http://example.com/');
        $element = $domDocument->getElementById('example');

        $link = new Link($uri, $element);

        $this->assertSame($uri, $link->getUri());
        $this->assertSame($element, $link->getElement());
        $this->assertEquals($elementSource, $link->getElementAsString());
    }

    public function testCreateNoOwnerDocument()
    {
        $element = new \DOMElement('name', 'value', 'uri');
        $uri = new Uri('http://example.com/');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('element must have an ownerDocument');

        new Link($uri, $element);
    }
}
