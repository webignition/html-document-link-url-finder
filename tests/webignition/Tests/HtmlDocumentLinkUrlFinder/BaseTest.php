<?php

namespace webignition\Tests\HtmlDocumentLinkUrlFinder;

use webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder;

abstract class BaseTest extends \PHPUnit_Framework_TestCase {    
    
    
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
        return new HtmlDocumentLinkUrlFinder();
    }      
    
}