HTML Document Link URL Finder
=============================

Get a collection of absolute urls, with their associated elements, for links in a HTML document.

Usage
-----

### Getting a LinkCollection from a WebPage

```php
use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;
use webignition\WebResource\WebPage\WebPage;

$webPageUrl = 'http://www.google.co.uk/search?q=Hello+World';
$webPage = WebPage::createFromContent((string) file_get_contents($sourceUrl));

$finder = new HtmlDocumentLinkUrlFinder();
$linkCollection = $finder->getLinkCollection($webPage, $webPageUrl);
```

### Accessing a LinkCollection

```php
use Psr\Http\Message\UriInterface;

// Assuming $linkCollection from previous example

// Iterating
foreach ($linkCollection as $link) {
    $link->getUri();      // UriInterface instance
    $link->getElement();  // \DOMElement instance
}

// Counting
count($linkCollection);

// Get URIs only
$linkCollection->getUris(); // array of UriInterface

// Get unique URIs only
$linkCollection->getUniqueUris(); // array of UriInterface
```

### Filtering a LinkCollection

All `LinkCollection::filterBy*()` methods return a new `LinkCollection` instance.

```php
use webignition\Uri\ScopeComparer;

// Filtering
$anchorLinks = $linkCollection->filterByElementName('a');
$elementsWithRelStylesheetAttribute = $linkCollection->filterByAttribute('rel', 'stylesheet');
$linksWithinUrlScope = $linkCollection->filterByUrlScope(
    new ScopeComparer(),
    ['http://example.com/']
);

$linkElementsWithRelStylesheetAttribute = $linkCollection
    ->filterByElementName('link')
    ->filterByAttribute('rel', 'stylesheet');

```
