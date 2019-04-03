<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

use Psr\Http\Message\UriInterface;
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

        $webPageBaseUrl = $webPage->getBaseUrl();
        $baseUri = new Uri((empty($webPageBaseUrl)) ? $webPageUrl : $webPageBaseUrl);

        $elements = $this->filterElements(
            $webPage->getInspector()->querySelectorAll('[href], [src]')
        );
        $uris = $this->getUrisFromElements($elements, $baseUri);

        foreach ($uris as $index => $uri) {
            $links[] = new Link($uri, $elements[$index]);
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
     * @param \DOMElement[] $elements
     * @param UriInterface $baseUri
     *
     * @return UriInterface[]
     */
    private function getUrisFromElements(array $elements, UriInterface $baseUri): array
    {
        $uris = [];

        foreach ($elements as $element) {
            $elementUrlValue = $element->hasAttribute(self::HREF_ATTRIBUTE_NAME)
                ? $element->getAttribute(self::HREF_ATTRIBUTE_NAME)
                : $element->getAttribute(self::SRC_ATTRIBUTE_NAME);

            $uri = AbsoluteUrlDeriver::derive($baseUri, new Uri($elementUrlValue));
            $uri = Normalizer::normalize($uri);

            $uris[] = $uri;
        }

        return $uris;
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
            if (!$this->elementExcluder->isExcluded($element) && $this->hasUrlAttribute($element)) {
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
}
