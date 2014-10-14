<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;
use webignition\WebResource\WebPage\WebPage;

abstract class BaseTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var HtmlDocumentLinkUrlFinder
     */
    private $finder;

    /**
     * @return string
     */
    abstract protected function getFixtureName();


    /**
     * @return string
     */
    abstract protected function getSourceUrl();


    public function setUp() {
        $this->finder = new HtmlDocumentLinkUrlFinder();

        $source = new WebPage();
        $source->setHttpResponse(\Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\nContent-Type:text/html"));
        $source->setContent($this->getFixture($this->getFixtureName()));

        $this->finder->getConfiguration()->setSource($source);
        $this->finder->getConfiguration()->setSourceUrl($this->getSourceUrl());
    }
    
    
    /**
     * 
     * @param string $name
     * @return string
     */
    protected function getFixture($name) {
        return file_get_contents(__DIR__ . '/../../../fixtures/' . $name . '.html');
    }
    
    
    /**
     *
     * @return \webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder
     */
    protected function getFinder() {
        return $this->finder;
    }
    
}