<?php

// require_once '../pms.php';
require_once '../autoload.php';

use repo\AutomationRepository;
use server\Database;

$repository = new AutomationRepository(new Database);

$length = $_POST['length'];
$start = $_POST['start'];
$sql = $_POST['sqlData'];
$draw = $_POST['draw'];
$totalRecords = $_POST['totalRecords'];

$config = [$length, $start, $draw, $sql, $totalRecords];
$repository->fetchAutomations($config);

?>