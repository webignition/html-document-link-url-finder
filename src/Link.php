<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use Psr\Http\Message\UriInterface;

class Link
{
    private $url;
    private $element;

    public function __construct(UriInterface $uri, \DOMElement $element)
    {
        $this->url = $uri;

        if (empty($element->ownerDocument)) {
            throw new \InvalidArgumentException('element must have an ownerDocument');
        }

        $this->element = $element;
    }

    public function getUri(): UriInterface
    {
        return $this->url;
    }

    public function getElement(): \DOMElement
    {
        return $this->element;
    }

    public function getElementAsString(): string
    {
        return $this->element->ownerDocument->saveHTML($this->element);
    }
}
