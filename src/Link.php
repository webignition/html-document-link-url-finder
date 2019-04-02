<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

class Link
{
    private $url;
    private $element;

    public function __construct(string $url, string $element)
    {
        $this->url = $url;
        $this->element = $element;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getElement(): string
    {
        return $this->element;
    }
}
