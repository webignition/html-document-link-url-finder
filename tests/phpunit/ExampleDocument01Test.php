<?php
ini_set('display_errors', 'On');
require_once(__DIR__.'/../../lib/bootstrap.php');

class ExampleDocument01Test extends AbstractDataSourceTest {
    
    private $expectedLinks = array(
        'http://example.com/path-level-one/relative-path',
        'http://example.com/root-relative-path',
        'http://example.com/protocol-relative-same-host',
        'http://another.example.com/protocol-relative-same-host',
        'http://example.com/path-level-one/#fragment-only',
        'http://www.youtube.com/example',
        'http://blog.example.com',
        'http://twitter.com/example'
    );
    
    public function testLinkFinding() {
        $this->setDataFile('/example01.html');

        $finder = new \webignition\HtmlDocumentLinkUrlFinder\HtmlDocumentLinkUrlFinder();
        $finder->setSourceContent($this->getTestData());
        $finder->setSourceUrl('http://example.com/path-level-one');

        $urls = $finder->urls();
        
        $this->assertEquals(count($this->expectedLinks), count($urls));
        
        foreach ($urls as $index => $url) {
            $this->assertEquals($this->expectedLinks[$index], $url);
        }        
    }    
  
}