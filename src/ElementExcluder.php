<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

class ElementExcluder
{
    /**
     * @var array
     */
    private $ignoredElements = [
        'base' => [],
        'link' => [
            'rel' => 'dns-prefetch',
        ],
    ];

    public function isExcluded(\DOMElement $element): bool
    {
        $elementNodeName = $element->nodeName;

        foreach ($this->ignoredElements as $nodeName => $attributeMatchers) {
            if ($elementNodeName === $nodeName) {
                return $this->matchesAttributes($element, $attributeMatchers);
            }
        }

        return false;
    }

    private function matchesAttributes(\DOMElement $element, array $attributeMatchers): bool
    {
        if (empty($attributeMatchers)) {
            return true;
        }

        $matches = true;

        foreach ($attributeMatchers as $name => $value) {
            $name = (string) $name;

            if ($element->getAttribute($name) !== $value) {
                $matches = false;
            }
        }

        return $matches;
    }
}
