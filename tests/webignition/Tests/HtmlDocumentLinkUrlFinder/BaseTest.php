<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;
use webignition\WebResource\WebPage\WebPage;
use GuzzleHttp\Message\MessageFactory as HttpMessageFactory;
use GuzzleHttp\Message\ResponseInterface as HttpResponse;

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
        $source->setHttpResponse($this->getHttpResponseFromMessage("HTTP/1.1 200 OK\nContent-Type:text/html"));
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

    /**
     * @param $message
     * @return HttpResponse
     */
    protected function getHttpResponseFromMessage($message) {
        $factory = new HttpMessageFactory();
        return $factory->fromMessage($message);
    }
    
}