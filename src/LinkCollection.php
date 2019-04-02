<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\Uri\ScopeComparer;
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

        foreach ($this as $link) {
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
        $name = trim(strtolower($name));

        return $this->filter(function (Link $link) use ($name) {
            return strtolower($link->getElement()->nodeName) === $name;
        });
    }

    public function filterByAttribute(string $name, string $value): LinkCollection
    {
        return $this->filter(function (Link $link) use ($name, $value) {
            return $link->getElement()->getAttribute($name) === $value;
        });
    }

    public function filterByUriScope(ScopeComparer $scopeComparer, array $scopes): LinkCollection
    {
        return $this->filter(function (Link $link) use ($scopeComparer, $scopes) {
            foreach ($scopes as $scope) {
                if ($scopeComparer->isInScope($scope, $link->getUri())) {
                    return true;
                }
            }

            return false;
        });
    }

    private function filter(callable $matcher)
    {
        $filteredLinks = [];

        foreach ($this as $link) {
            if ($matcher($link)) {
                $filteredLinks[] = $link;
            }
        }

        return new LinkCollection($filteredLinks);
    }
}
