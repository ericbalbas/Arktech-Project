<?php
require_once 'pms.php';
require_once 'autoload.php';

use server\Database;
use repo\AutomationRepository;

$database = new Database;
$repository = new AutomationRepository($database, true);

# Fetch the available for automation
$availablForAuto = $database::fetchSql("SELECT lotNumber FROM system_forautomate WHERE DATE(dateInput) = CURDATE() AND status = 0");
foreach($availablForAuto as $auto)
{
    //Check if it is really meant for automation 
    $repository->automateProcess($auto->lotNumber);
}





?>