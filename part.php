<?php

/**
 * PAR is a PHP Applications Requirements Tester
 * Class for testing application requirements 
 * 
 * @author Jacek Skirzynski <jacek@skirzynski.eu>
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
     */
    public function checkPHPVersion($version, $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->addResult('PHP version', version_compare(PHP_VERSION, $version, $operator), $version, PHP_VERSION, $operator);
    }

    /**
     * Check the extension is loaded
     * You can too check the version of extension
     * @param string $name
     * @param string $version
     * @param string $operator
     */
    public function checkExtensionLoaded($name, $version = null, $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        if ($version) {
            $value = phpversion($name);
            $result = version_compare($value, $version, $operator);
        } else {
            $result = extension_loaded($name);
            $operator = '';
        }

        $this->addResult('Extension loaded [' . $name . ']', $result, $version, $value, $operator);
    }

    /**
     * Check the extensions are loaded
     * @param array $names array of names extensions to check
     */
    public function checkExtensionsLoaded(array $names)
    {
        foreach ($names as $name) {
            $this->addResult('Extension loaded [' . $name . ']', extension_loaded($name));
        }
    }

    /**
     * Check the config value
     * @param string $name name of php.ini directive for checking
     * @param mixed $expected expected value of directive
     */
    public function checkConfigHasValue($name, $expected, $operator = self::COMPARE_EQUAL)
    {
        $value = ini_get($name);

        $this->addResult('Config [' . $name . ']', $this->compare($value, $expected, $operator), $expected, $value);
    }

    /**
     * Check the functions are not disabled
     * @param array $functions
     */
    public function checkNotDisabledFunctions(array $functions)
    {
        $this->checkNotDisabled(self::TYPE_AVAILABLE_FUNCTION, $functions);
    }

    /**
     * Check the classes are not disabled
     * @param array $classes
     */
    public function checkNotDisabledClass(array $classes)
    {
        $this->checkNotDisabled(self::TYPE_AVAILABLE_CLASS, $classes);
    }

    /**
     * Check the MySQL support extension is available
     * @param mixed $version
     * @param string $operator
     */
    public function checkMySQL($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('mysql', $version, $operator);
    }

    /**
     * Check the MySQLi support extension is available
     * @param mixed $version
     * @param string $operator
     */
    public function checkMySQLi($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('mysqli', $version, $operator);
    }

    /**
     * Check the PostgreSQL support extension is available
     * @param mixed $version
     * @param string $operator
     */
    public function checkPostgreSQLi($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('pgsql', $version, $operator);
    }

    /**
     * Check the PDO module is available
     * @param mixed $version
     * @param string $operator
     */
    public function checkPDO($version = '', $operator = self::COMPARE_GREATER_THAN_OR_EQUAL)
    {
        $this->checkExtensionLoaded('PDO', $version, $operator);
    }

    /**
     * Check the PDO driver is available
     * @param string $name
     */
    public function checkPDODriver($name)
    {
        $drivers = array();
        if (function_exists('pdo_drivers')) {
            $drivers = pdo_drivers();
        }

        $this->addResult('PDO driver [' . $name . ']', in_array($name, $drivers));
    }

    /**
     * Check the OS is MS Windows
     */
    public function checkWindowsServer()
    {
        $this->addResult('Windows serwer', $this->checkWindowsOs());
    }

    /**
     * Check the OS isn't MS Windows
     */
    public function checkNotWindowsServer()
    {
        $this->addResult('Not Windows serwer', !$this->checkWindowsOs());
    }

    public function __destruct()
    {
        echo "Test name\t\tResult\tExpected value\tSystem value" . PHP_EOL;

        foreach ($this->results as $record) {
            echo $record['name'] . "\t\t" .
            (($record['result']) ? 'OK' : 'failure') . "\t" .
            $record['expected'] .
            (($record['operator']) ? " (" . $record['operator'] . ")\t" : '') .
            $record['value'] . PHP_EOL;
        }
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
