<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

class LinkCollection
{
    /**
     * @var Link[]
     */
    private $links;

    public function __construct(array $links = [])
    {
        foreach ($links as $link) {
            if ($link instanceof Link) {
                $this->links[] = $link;
            }
        }
    }
}
