<?php
ini_set('display_errors', 'On');
require_once(__DIR__.'/../../lib/bootstrap.php');

class AbstractDataSourceTest extends PHPUnit_Framework_TestCase {
    
    const DATA_PATH= '/../data';
    
    private $dataFile = null;
    
    /**
     *
     * @return string
     */
    private function dataPath() {
        return __DIR__ . self::DATA_PATH;
    }
    
    protected function setDataFile($dataFile) {
        $this->dataFile = $dataFile;
    }
    
    private function dataFilePath() {
        return $this->dataPath() . $this->dataFile;
    }
    
    protected function getTestData() {
        return file_get_contents($this->dataFilePath());
    }
    
    public function testAbstractTest() {
        
    }    
  
}