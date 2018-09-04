<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

class ElementExcluder
{
    const BASE_ELEMENT_NAME = 'base';

    /**
     * @var array
     */
    private $ignoredElements = [
        'base' => [],
        'link' => [
            'rel' => 'dns-prefetch',
        ],
    ];

    /**
     * @param \DOMElement $element
     *
     * @return boolean
     */
    public function isExcluded(\DOMElement $element)
    {
        $elementNodeName = $element->nodeName;

        foreach ($this->ignoredElements as $nodeName => $attributeMatchers) {
            if ($elementNodeName === $nodeName) {
                return $this->matchesAttributes($element, $attributeMatchers);
            }
        }

        return false;
    }

    /**
     * @param \DOMElement $element
     * @param array $attributeMatchers
     *
     * @return bool
     */
    private function matchesAttributes(\DOMElement $element, array $attributeMatchers)
    {
        if (empty($attributeMatchers)) {
            return true;
        }

        $matches = true;

        foreach ($attributeMatchers as $name => $value) {
            if (!$element->hasAttribute($name)) {
                $matches = false;
            }

            if ($element->getAttribute($name) !== $value) {
                $matches = false;
            }
        }

        return $matches;
    }
}
