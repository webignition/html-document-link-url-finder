<?php
ini_set('display_errors', 'On');
ini_set('max_execution_time', 600);
require_once($_SERVER['DOCUMENT_ROOT'].'/../../lib/bootstrap.php');

$sourceUrls = array(
    'http://news.bbc.co.uk/1/hi/help/3681938.stm',
    'http://www.limegreentangerine.co.uk/branding/',
    'http://en.wikipedia.org/wiki/Main_Page',
    'http://www.google.co.uk',
    'http://news.bbc.co.uk/',
    'http://reddit.com',
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