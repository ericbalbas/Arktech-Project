<?php
require_once 'pms.php';
require_once 'autoload.php';

use server\Database;
use repo\AutomationRepository;

$database = new Database;
$repository = new AutomationRepository($database, true);

$testCase = '20-20-2000'; // poId here or booking deciding here!!!

//Check if it is really meant for automation 
$repository->automateProcess($testCase);
//get details
//check quantity if quantity is exact to desired amount then as is
// if quantity has remainder then partial 
// get booking 
// get nesting drawing and program
// place to nesting folders and finish process and booking 



?>