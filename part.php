<?php

/**
 * PART is a PHP Applications Requirements Tester
 * 
 * @author Jacek Skirzynski <jacek@skirzynski.eu>
 * @license http://opensource.org/licenses/MIT MIT
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jacek Skirzyński
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
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
     * Instance of report
     * @var ReportInterface
     */
    protected $report;
    
    /**
     * Was report generated?
     * @var boolean
     */
    protected $generatedReport = false;

    /**
     * Create object with specified type of report or depends on run environment 
     * @param ReportInterface $report
     */
    public function __construct(ReportInterface $report = null)
    {
        if ($report) {
            $this->report = $report;
        } else {
            $this->report = ReportFactory::factory();
        }
    }

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
    
    /**
     * Check the default timezone
     * @link http://www.php.net/manual/en/timezones.php
     * @param string $timezone
     * @return PART
     */
    public function checkDefaultTimezone($timezone)
    {
        $this->addResult(
            'Default timezone', 
            (date_default_timezone_get() == $timezone), 
            $timezone, 
            date_default_timezone_get()
        );
        return $this;
    }
    
    /**
     * Generating the report of tests
     */
    public function generateReport()
    {
        $this->generatedReport = true;
        $this->report->generate($this->results);
    }

    /**
     * Generating the report if it wasn't generated by manually invoking
     */
    public function __destruct()
    {
        if (!$this->generatedReport) {
            $this->generateReport();
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
     * Check the elements (functions or classes) are not disabled
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
 * Interface for reports
 */
interface ReportInterface
{
    /**
     * Generate report in a specified format
     * @param array $data array of results tests
     */
    public function generate(array $data);
}

/**
 * Factory class for reports
 */
class ReportFactory
{
    /**
     * Return report instance based on type of call
     * @static
     * @param string $type name of type
     * @return ReportInterface
     */
    public static function factory($type = null)
    {
        if (is_null($type)) {
            if ('cli' == php_sapi_name()) {
                return new ConsoleReport();
            } else {
                return new WebReport();
            }
        } else {
            $className = ucfirst($type) . 'Report';
            if (class_exists($className)) {
                return new $className;
            } else {
                throw new InvalidArgumentException('Invalid type of report: '. $className);
            }
        }
    }
}

/**
 * Report console format
 */
class ConsoleReport implements ReportInterface
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
        echo 'PHP Applications Requirements Tester - Report' . PHP_EOL;
        $this->printWarning();
        echo sprintf(
            '%-40s %-10s %-10s %-10s',
            'Test name',
            'Result',
            'Expected',
            'Environment'
        ) 
            . PHP_EOL 
            . str_repeat('=', 80)
            . PHP_EOL;
    }
    
    /**
     * Print warning about different between web and CLI envirionment
     */
    protected function printWarning()
    {
        echo PHP_EOL . 'Warning: The CLI environment may be different from web environment' . PHP_EOL;
    }
}

/**
 * Report web format
 */
class WebReport implements ReportInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(array $data)
    {
        $this->printWebHeader();
        
        echo '<tr>
            <th>Test name</th>
            <th>Result</th>
            <th>Expected</th>
            <th>Environment</th>
        </tr>';
        
        foreach ($data as $result) {
            $expected = $result['expected'];            
            if ($result['operator']) {
                $expected .= ' [' . $result['operator'] .']';
            }
            
            echo '<tr>
                <td class="left">'. $result['name'] .' </td>
                <td class="'. (($result['result']) ? 'success' : 'failure') .'">'. (($result['result']) ? 'OK' : 'failure') .'</td>
                <td>'. $expected .'</td>
                <td>'. $result['value'] .'</td>
                </tr>';
        }
        
        $this->printWebFooter();
    }    
    
    /**
     * Print header 
     */
    protected function printWebHeader()
    {
        echo '<?xml version="1.0" encoding="UTF-8"?>
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>PHP Applications Requirements Tester - Report</title>
                <style type="text/css">
                body {font-family: sans-serif;}
                table {border-collapse: collapse;border-width: 1px;border-style: outset;border-color: gray; margin-right: auto; margin-left: auto;}
                td,th {text-align: center; padding: 5px;border-style: inset;border-width: 1px; border-color: gray;}
                tr:nth-child(even) {background-color: #EEE;}
                .left {text-align: left;}
                .success {color: green; font-weight: bold; text-align: center;}
                .failure {color: red; font-weight: bold; text-align: center;}
                </style>
            </head>
            <body>
            <table>
                <tr>
                    <th colspan="4">PHP Applications Requirements Tester - Report</th>
                </tr>';
    }
    
    /**
     * Print footer
     */
    protected function printWebFooter()
    {
        echo '</table></body></html>';
    }
}

/**
 * Report which stores only results
 */
class DataReport implements ReportInterface, Iterator
{
    /**
     * Value of current key/position
     * @var integer
     */
    protected $currentKey = 0;

    /**
     * Results array
     * @var array
     */
    protected $data = array();

    /**
     * {@inheritdoc}
     */
    public function generate(array $data)
    {
        foreach ($data as $result) {
            $object = new stdClass();
            $object->name = $result['name'];
            $object->value = $result['value'];
            $object->expected = $result['expected'];
            $object->result = $result['result'];
            $object->operator = $result['operator'];
            
            array_push($this->data, $object);
        }
    }
    
    /**
     * Get array of results
     * @return array
     */
    public function getArray()
    {
        return $this->data;
    }

    public function current()
    {
        return $this->data[$this->currentKey];
    }

    public function key()
    {
        return $this->currentKey;
    }

    public function next()
    {
        $this->currentKey += 1;
    }

    public function rewind()
    {
        $this->currentKey = 0;
    }

    public function valid()
    {
        return array_key_exists($this->currentKey, $this->data);
    }

}
