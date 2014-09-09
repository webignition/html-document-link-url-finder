<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;

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
        $this->finder->getConfiguration()->setSourceContent($this->getFixture($this->getFixtureName()));
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