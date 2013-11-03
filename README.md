# PART

## Overview
PART is a **P**HP **A**pplications **R**equirements **T**ester. 

PART allows you to check if the hosting server meets application requirements.

## Using
Using of PART is very simple - you only have to invoke tests for what you need.

### Versions
Many of the tests may check version e.g. extension. If you want to check version you have to put it in "PHP-standardized" version number strings (see http://www.php.net/manual/en/function.version-compare.php) as parameter of test.

### Operators
If you want to check version you can specify a operator using defined class constants, you can see a list of available operators below:

* `PART::COMPARE_EQUAL`
* `PART::COMPARE_NOT_EQUAL`
* `PART::COMPARE_GREATER_THAN`
* `PART::COMPARE_GREATER_THAN_OR_EQUAL`
* `PART::COMPARE_LESS_THAN`
* `PART::COMPARE_LESS_THAN_OR_EQUAL`

### List of tests
You can see a list of current available tests below:

* check PHP version
* check loaded extension
* check PHP configurations value
* check disabled_functions and disabled_classes
* check extensions: MySQL, MySQLi, PostgreSQL, PDO are loaded
* check PDO drivers
* check Windows on host
* check IonCube extension
* check Magic Quotes
* check default Time Zone

## Reports
Reports define what, it will do after the all tests. PART has built-in 3 kind of report.

You can define type of report which you want to get, by constructor parameter:

`$part = new PART(new DataReport());`

If you don't specify kind of report, PART will decide about this itself based on run environment. 

### ConsoleReport
ConsoleReport generates human-readable report for viewing in console. This type is default for CLI environments.

### WebReport
WebReport generates report as HTML for viewing in web browser. This type is default for invoking by web browser.

### DataReport
DataReport only stores objects array of results tests. Object DataReport implements Iterator interface, so you can use it with foreach. Second way to using results is invoke `getArray()` for getting pure array. 

DataReport stores only results of tests in object and doesn't generate human-readable reports. For using this type you have to create it and pass it by parameter to PART constructor. Additionaly you have to add invoke `generateReport()` after the last test.

## Author
Jacek Skirzyński, <http://skirzynski.eu/>