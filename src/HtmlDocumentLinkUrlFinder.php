<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use webignition\AbsoluteUrlDeriver\AbsoluteUrlDeriver;
use webignition\Uri\Normalizer;
use webignition\Uri\Uri;
use webignition\WebResource\WebPage\WebPage;

class HtmlDocumentLinkUrlFinder
{
    const HREF_ATTRIBUTE_NAME  = 'href';
    const SRC_ATTRIBUTE_NAME  = 'src';

    /**
     * @var Configuration
     */
    private $configuration = null;

    /**
     * @var ElementExcluder
     */
    private $elementExcluder;

    public function __construct()
    {
        $this->elementExcluder = new ElementExcluder();
        $this->configuration = new Configuration();
    }

    public function getLinkCollection(WebPage $webPage, string $webPageUrl): LinkCollection
    {
        $links = [];

        $urls = $this->getAllUrls($webPage, $webPageUrl);
        $elements = $this->getRawElements($webPage);

        foreach ($urls as $index => $url) {
            $links[] = new Link(
                new Uri($url),
                $elements[$index]
            );
        }

        return new LinkCollection($links);
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param WebPage $webPage
     * @param string $webPageUrl
     *
     * @return string[]
     */
    private function getAllUrls(WebPage $webPage, string $webPageUrl): array
    {
        $urls = [];
        $elements = $this->getRawElements($webPage);

        if (empty($elements)) {
            return [];
        }

        $baseUri = new Uri($this->getBaseUrl($webPage, $webPageUrl));

        foreach ($elements as $element) {
            $uri = AbsoluteUrlDeriver::derive(
                $baseUri,
                new Uri($this->getUrlValueFromElement($element))
            );

            $uri = Normalizer::normalize($uri);

            $urls[] = (string) $uri;
        }

        return $urls;
    }

    private function getRawElements(WebPage $webPage): array
    {
        $elementsWithUrlAttributes = $this->getElementsWithUrlAttributes($webPage);
        $elements = [];

        if (empty($elementsWithUrlAttributes)) {
            return [];
        }

        foreach ($elementsWithUrlAttributes as $element) {
            $elements[] = $element;
        }

        return $elements;
    }

    private function getUrlValueFromElement(\DOMElement $element): string
    {
        if ($element->hasAttribute(self::HREF_ATTRIBUTE_NAME)) {
            return $element->getAttribute(self::HREF_ATTRIBUTE_NAME);
        }

        return $element->getAttribute(self::SRC_ATTRIBUTE_NAME);
    }

    private function getElementsWithUrlAttributes(WebPage $webPage): array
    {
        $elements = $webPage->getInspector()->querySelectorAll('[href], [src]');
        $filteredElements = $this->filterElements($elements);

        return $filteredElements;
    }

    /**
     * @param \DOMElement[] $elements
     *
     * @return \DOMElement[]
     */
    private function filterElements(array $elements): array
    {
        $filteredElements = [];

        foreach ($elements as $element) {
            $includeElement = !$this->elementExcluder->isExcluded($element);
            $includeElement = $includeElement && $this->hasUrlAttribute($element);

            if ($includeElement) {
                $filteredElements[] = $element;
            }
        }

        return $filteredElements;
    }

    private function hasUrlAttribute(\DOMElement $element): bool
    {
        $hasHrefAttribute = $element->hasAttribute(self::HREF_ATTRIBUTE_NAME);
        $hasSrcAttribute = $element->hasAttribute(self::SRC_ATTRIBUTE_NAME);

        $attributeValue = '';

        if ($hasHrefAttribute) {
            $attributeValue = $element->getAttribute(self::HREF_ATTRIBUTE_NAME);
        } elseif ($hasSrcAttribute) {
            $attributeValue = $element->getAttribute(self::SRC_ATTRIBUTE_NAME);
        }

        if ($this->looksLikeConcatenatedJsString($attributeValue)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the URL value from an element attribute looks like a concatenated JS string
     *
     * Some documents contain script elements which concatenate JS values together to make URLs for elements that
     * are inserted into the DOM. This is all fine.
     * e.g. VimeoList.push('<img src="' + value['ListImage'] + '" />');
     *
     * A subset of such documents are badly-formed such that the script contents are not recognised by the DOM parser
     * and end up as element nodes in the DOM
     * e.g. the above would result in a <img src="' + value['ListImage'] + '" /> element present in the DOM.
     *
     * This applies to both the \DOMDocument parser and the W3C HTML validation parser. It is assumed both parsers
     * are not identically buggy.
     *
     * We need to check if a URL value looks like a concatenated JS string so that we can exclude them.
     *
     * Example URL value: ' + value['ListImage'] + '
     * Pseudopattern: START'<whitespace?><plus char><whitespace?><anything><whitespace?><plus char><whitespace?>'END
     *
     * @param string $url
     *
     * @return bool
     */
    private function looksLikeConcatenatedJsString(string $url): bool
    {
        $patternBody = "^'\s+\+\s+.*\s+\+\s+'$";
        $pattern = '/'.$patternBody.'/i';

        return preg_match($pattern, $url) > 0;
    }

    private function getBaseUrl(WebPage $webPage, string $webPageUrl): string
    {
        $webPageBaseUrl = $webPage->getBaseUrl();

        return (empty($webPageBaseUrl))
            ? $webPageUrl
            : $webPageBaseUrl;
    }
}
