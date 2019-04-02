<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use Psr\Http\Message\UriInterface;

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

    /**
     * @return UriInterface[]
     */
    public function getUris(): array
    {
        $uris = [];

        foreach ($this->links as $link) {
            $uris[] = $link->getUri();
        }

        return $uris;
    }

    public function getUniqueUris(bool $ignoreFragment = false): array
    {
        $allUris = $this->getUris();
        $uniqueUris = [];

        foreach ($allUris as $uri) {
            if ($ignoreFragment) {
                $uri = $uri->withFragment('');
            }

            $uniqueUris[(string) $uri] = $uri;
        }

        return array_values($uniqueUris);
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

    public function filterByAttribute(string $name, string $value): LinkCollection
    {
        $filteredLinks = [];

        foreach ($this as $link) {
            $element = $link->getElement();

            if ($element->getAttribute($name) === $value) {
                $filteredLinks[] = $link;
            }
        }

        return new LinkCollection($filteredLinks);
    }
}
