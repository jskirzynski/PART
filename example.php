<?php

require_once 'part.php';

$obiekt = new PART();
$obiekt->checkConfigHasValue('display_errors', 0)
    ->checkPDO()
    ->checkPDODriver('mysql')
    ->checkPHPVersion(5.4)
    ->checkDisableMagicQuotes()
    ->checkDefaultTimezone('Europe/Warsaw')
    ->checkNotWindowsServer();
