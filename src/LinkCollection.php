<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\Uri\Uri;

class LinkCollection implements \Iterator, \Countable
{
    /**
     * @var Link[]
     */
    private $links;

    private $iteratorPosition = 0;

    public function __construct(array $links = [])
    {
        foreach ($links as $link) {
            if ($link instanceof Link) {
                $this->links[] = $link;
            }
        }

        $this->iteratorPosition = 0;
    }

    public function count(): int
    {
        return count($this->links);
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current(): Link
    {
        return $this->links[$this->iteratorPosition];
    }

    public function key()
    {
        return $this->iteratorPosition;
    }

    public function next()
    {
        ++$this->iteratorPosition;
    }

    public function valid()
    {
        return isset($this->links[$this->iteratorPosition]);
    }

    public function getUrls(): array
    {
        $urls = [];

        foreach ($this->links as $link) {
            $urls[] = $link->getUrl();
        }

        return $urls;
    }

    public function getUniqueUrls(bool $ignoreFragment = false): array
    {
        $allUrls = $this->getUrls();
        $uniqueUrls = [];

        foreach ($allUrls as $url) {
            if ($ignoreFragment) {
                $url = $this->createUniquenessComparisonUrl($url);
            }

            $uniqueUrls[$url] = $url;
        }

        return array_values($uniqueUrls);
    }

    public function filterByElementName(string $name): LinkCollection
    {
        $comparatorName = trim(strtolower($name));
        $filteredLinks = [];

        foreach ($this as $link) {
            $element = $link->getElement();

            if (strtolower($element->nodeName) === $comparatorName) {
                $filteredLinks[] = $link;
            }
        }

        return new LinkCollection($filteredLinks);
    }

    private function createUniquenessComparisonUrl(string $url): string
    {
        $uri = new Uri($url);
        $uri = $uri->withFragment('');

        return (string) $uri;
    }
}
