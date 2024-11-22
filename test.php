<?php
require_once 'pms.php';
require_once 'autoload.php';

use server\Database;
use repo\AutomationRepository;

$database = new Database;
$repository = new AutomationRepository($database, true);

# Fetch the available for automation
$availablForAuto = $database::fetchSql("SELECT lotNumber FROM view_workschedule WHERE lotNumber IN (SELECT lotNumber FROM system_forautomate WHERE status NOT IN (1,2)) AND processCode IN (430,312) AND availability =1 ");
foreach($availablForAuto as $auto)
{
    //Check if it is really meant for automation 
    $repository->automateProcess($auto->lotNumber);
}


// print_r($availablForAuto);




?>