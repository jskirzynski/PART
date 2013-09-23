<?php

/**
 * Class for testing application requirements 
 * 
 * @author Jacek Skirzynski <jacek@skirzynski.eu>
 */
class PHPApplicationRequirement
{
    const COMPARE_EQUAL = '==';
    const COMPARE_NOT_EQUAL = '<>';
    const COMPARE_GREATER_THAN = '>';
    const COMPARE_GREATER_THAN_OR_EQUAL = '>=';
    const COMPARE_LESS_THAN = '<';
    const COMPARE_LESS_THAN_OR_EQUAL = '<=';
    
    /**
     * Results of tests
     * @var array
     */
    protected $results = array();


    /**
     * Check the PHP version
     * @param string $version version of PHP in "PHP-standardized" format 
     * (http://www.php.net/manual/en/function.version-compare.php)
     * @param string $operator
     */
    public function isPHPVersion($version, $operator=self::COMPARE_GREATER_THAN_OR_EQUAL) {
        $this->addResult('PHP version', 
            PHP_VERSION, 
            $version, 
            version_compare(PHP_VERSION, $version, $operator), 
            $operator);
    }

    public function __destruct() {
        echo "Test name\t\tResult\tExpected value\tSystem value". PHP_EOL;
        
        foreach ($this->results as $record) {
            echo $record['name'] ."\t\t". 
                (($record['result']) ? 'OK' : 'failure') ."\t".
                $record['expected'] 
                ." (". $record['operator'] .")\t".
                $record['value'] . PHP_EOL;
        }
    }

    /**
     * Add test results to array
     * @param string $name name of test
     * @param string $value value for test from system
     * @param string $expected value expected for user 
     * @param boolean $result result of comparison system value and expected value
     * @param string $operator used operator
     */
    protected function addResult($name, $value, $expected, $result, $operator) {
        array_push($this->results, array(
            'name' => $name,
            'value' => $value,
            'expected' => $expected,
            'result' => (bool)$result,
            'operator' => $operator
        ));
    }
}
