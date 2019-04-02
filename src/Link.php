<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

class Link
{
    private $url;
    private $element;

    public function __construct(string $url, \DOMElement $element)
    {
        $this->url = $url;

        if (empty($element->ownerDocument)) {
            throw new \InvalidArgumentException('element must have an ownerDocument');
        }

        $this->element = $element;
    }

    public function getUrl(): string
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
