HTML Document Link URL Finder
=============================

Links in an HTML document might be relative or protocol-less. Get a collection of full absolute URLs for links in an HTML document.

Usage
-----

### The "Hello World" example

    $sourceUrl = 'http://www.google.co.uk/search?q=Hello+World';


    echo "Finding link URLs in ".$sourceUrl."\n";

    $sourceContent = file_get_contents($sourceUrl);

    $finder = new \webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder();
    $finder->setSourceContent($sourceContent);
    $finder->setSourceUrl($sourceUrl);

    $urls = $finder->urls();

    echo "Found ".count($urls)." urls\n";

    if (isset($_GET['verbose'])) {
        foreach ($urls as $url) {
            echo $url . "\n";
        }
    }

    echo "\n";