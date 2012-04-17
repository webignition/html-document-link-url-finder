<?php
ini_set('display_errors', 'On');
require_once($_SERVER['DOCUMENT_ROOT'].'/../../lib/bootstrap.php');

$sourceUrls = array(
    'http://www.limegreentangerine.co.uk',
    'http://en.wikipedia.org/wiki/Main_Page',
    'http://www.google.co.uk',
    'http://news.ycombinator.com',
    'http://news.bbc.co.uk/',
    'http://reddit.com',
    'http://news.ycombinator.com/item?id=3720332',
    'http://www.microsoft.com/en-us/default.aspx',
    'http://www.stackoverflow.com/'
);

foreach ($sourceUrls as $sourceUrl) {
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
}