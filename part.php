<?php

/**
 * PART is a PHP Applications Requirements Tester
 * 
 * @author Jacek Skirzynski <jacek@skirzynski.eu>
 */

/**
 * Class for testing application requirements 
 */
class PART
{
    const COMPARE_EQUAL = '==';
    const COMPARE_NOT_EQUAL = '<>';
    const COMPARE_GREATER_THAN = '>';
    const COMPARE_GREATER_THAN_OR_EQUAL = '>=';
    const COMPARE_LESS_THAN = '<';
    const COMPARE_LESS_THAN_OR_EQUAL = '<=';
    
    const TYPE_AVAILABLE_FUNCTION = 'function';
    const TYPE_AVAILABLE_CLASS = 'class';

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
     * @return PART
     */
    public function checkPHPVersion($version, $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->addResult(
            'PHP version', 
            version_compare(PHP_VERSION, $version, $operator), 
            $version, 
            PHP_VERSION, 
            $operator
        );
        
        return $this;
    }

    /**
     * Check the extension is loaded
     * You can too check the version of extension
     * @param string $name
     * @param string $version
     * @param string $operator
     * @return PART
     */
    public function checkExtensionLoaded($name, $version = null, $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        if ($version) {
            $value = phpversion($name);
            $result = version_compare($value, $version, $operator);
        } else {
            $result = extension_loaded($name);
            $operator = '';
            $value = '';
        }

        $this->addResult('Extension loaded [' . $name . ']', $result, $version, $value, $operator);
        return $this;
    }

    /**
     * Check the extensions are loaded
     * @param array $names array of names extensions to check
     * @return PART
     */
    public function checkExtensionsLoaded(array $names)
    {
        foreach ($names as $name) {
            $this->addResult('Extension loaded [' . $name . ']', extension_loaded($name));
        }
        
        return $this;        
    }

    /**
     * Check the config value
     * @param string $name name of php.ini directive for checking
     * @param mixed $expected expected value of directive
     * @return PART
     */
    public function checkConfigHasValue($name, $expected, $operator = self::COMPARE_EQUAL)
    {
        $value = ini_get($name);
        $this->addResult('Config [' . $name . ']', $this->compare($value, $expected, $operator), $expected, $value);
        return $this;
    }

    /**
     * Check the functions are not disabled
     * @param array $functions
     * @return PART
     */
    public function checkNotDisabledFunctions(array $functions)
    {
        $this->checkNotDisabled(self::TYPE_AVAILABLE_FUNCTION, $functions);
        return $this;
    }

    /**
     * Check the classes are not disabled
     * @param array $classes
     * @return PART
     */
    public function checkNotDisabledClass(array $classes)
    {
        $this->checkNotDisabled(self::TYPE_AVAILABLE_CLASS, $classes);
        return $this;
    }

    /**
     * Check the MySQL support extension is available
     * @param string $version
     * @param string $operator
     * @return PART
     */
    public function checkMySQL($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('mysql', $version, $operator);
        return $this;
    }

    /**
     * Check the MySQLi support extension is available
     * @param string $version
     * @param string $operator
     * @return PART
     */
    public function checkMySQLi($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('mysqli', $version, $operator);
        return $this;
    }

    /**
     * Check the PostgreSQL support extension is available
     * @param string $version
     * @param string $operator
     * @return PART
     */
    public function checkPostgreSQL($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('pgsql', $version, $operator);
        return $this;
    }

    /**
     * Check the PDO module is available
     * @param string $version
     * @param string $operator
     * @return PART
     */
    public function checkPDO($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('PDO', $version, $operator);
        return $this;
    }

    /**
     * Check the PDO driver is available
     * @param string $name
     * @return PART
     */
    public function checkPDODriver($name)
    {
        $drivers = array();
        if (function_exists('pdo_drivers')) {
            $drivers = pdo_drivers();
        }

        $this->addResult('PDO driver [' . $name . ']', in_array($name, $drivers));
        return $this;
    }

    /**
     * Check the OS is MS Windows
     * @return PART
     */
    public function checkWindowsServer()
    {
        $this->addResult('Windows OS serwer', $this->checkWindowsOs());
        return $this;
    }

    /**
     * Check the OS isn't MS Windows
     * @return PART
     */
    public function checkNotWindowsServer()
    {
        $this->addResult('Not Windows OS serwer', !$this->checkWindowsOs());
        return $this;
    }
    
    /**
     * Check the ionCube Loader is available
     * @param string $version
     * @param string $operator
     * @return PART
     */
    public function checkIonCube($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('ionCube Loader', $version, $operator);
        return $this;
    }

    /**
     * Check the Magic Quotes are disabled
     * @return PART
     */
    public function checkDisableMagicQuotes()
    {
        $this->checkConfigHasValue('magic_quotes_gpc', 0);
        $this->checkConfigHasValue('magic_quotes_runtime', 0);
        $this->checkConfigHasValue('magic_quotes_sybase', 0);
        return $this;
    }

    public function __destruct()
    {
        $report = AbstractReport::getInstance();
        $report->generate($this->results);
    }

    /**
     * Check the OS is MS Windows
     * @return boolean
     */
    protected function checkWindowsOs()
    {
        return (strtolower(php_uname('s')) == strtolower('windows'));
    }

    /**
     * Check the elements (functions or class) are not disabled
     * @param string $type
     * @param array $names
     * @throws Exception
     */
    protected function checkNotDisabled($type, array $names)
    {
        switch ($type) {
            case self::TYPE_AVAILABLE_FUNCTION:
                $disableString = ini_get('disable_functions');
                break;
            case self::TYPE_AVAILABLE_CLASS:
                $disableString = ini_get('disable_classes');
                break;
            default:
                throw new Exception('Available typ not implemented');
        }

        $disable = explode(',', $disableString);
        foreach ($names as $name) {
            $this->addResult('Not disable ' . $type . ' [' . $name . ']', !in_array($name, $disable));
        }
    }

    /**
     * Compare values by defined operator
     * @param mixed $value1
     * @param mixed $value2
     * @param string $operator
     * @return boolean
     * @throws Exception
     */
    protected function compare($value1, $value2, $operator = self::COMPARE_EQUAL)
    {
        switch ($operator) {
            case self::COMPARE_EQUAL:
                return ($value1 == $value2);
            case self::COMPARE_GREATER_THAN:
                return ($value1 > $value2);
            case self::COMPARE_GREATER_THAN_OR_EQUAL:
                return ($value1 >= $value2);
            case self::COMPARE_LESS_THAN:
                return ($value1 < $value2);
            case self::COMPARE_LESS_THAN_OR_EQUAL:
                return ($value1 <= $value2);
            case self::COMPARE_NOT_EQUAL:
                return ($value1 != $value2);
            default:
                throw new Exception('Compare operator not implemented');
        }
    }

    /**
     * Add test results to array
     * @param string $name name of test
     * @param boolean $result result of comparison system value and expected value
     * @param string $expected value expected for user 
     * @param string $value value for test from system
     * @param string $operator used operator
     */
    protected function addResult($name, $result, $expected = '', $value = '', $operator = '')
    {
        array_push($this->results, array(
            'name' => $name,
            'value' => $value,
            'expected' => $expected,
            'result' => (bool) $result,
            'operator' => $operator
        ));
    }
}

/**
 * Abstract class for report interface and provide report instances
 * @abstract
 */
abstract class AbstractReport
{
    /**
     * Return report instance based on type of call
     * @static
     * @return ConsoleReport
     * @throws Exception
     */
    public static function getInstance()
    {
        if ('cli' == php_sapi_name()) {
            return new ConsoleReport();
        }
        
        throw new Exception('Only CLI calls are supported at the moment');
    }
    
    /**
     * Generate report in a specified format
     * @abstract
     * @param array $data array of results tests
     */
    abstract public function generate(array $data);
}

/**
 * Report console format
 */
class ConsoleReport extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function generate(array $data)
    {
        $this->printHeader();
        
        foreach ($data as $result) {            
            $expected = $result['expected'];            
            if ($result['operator']) {
                $expected .= ' [' . $result['operator'] .']';
            }
            
            echo sprintf(
                '%-40s %-10s %-10s %-10s',
                $result['name'],
                (($result['result']) ? 'OK' : 'failure'),
                $expected,
                $result['value']
            ) . PHP_EOL;
        }
        
        $this->printWarning();
    }
    
    /**
     * Print header of report
     */
    protected function printHeader()
    {
        $this->printWarning();
        echo sprintf(
            '%-40s %-10s %-10s %-10s',
            'Test name',
            'Result',
            'Expected',
            'Environment'
        ) . PHP_EOL;
        
        for ($i = 0; $i < 80; $i++) {
            echo '=';
        }
        echo PHP_EOL;
    }
    
    /**
     * Print warning about different between web and CLI envirionment
     */
    protected function printWarning()
    {
        echo PHP_EOL . 'Warning: The CLI environment may be different from web environment' . PHP_EOL;
    }
}
