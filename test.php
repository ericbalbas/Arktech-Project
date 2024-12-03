<?php
require_once 'pms.php';
require_once 'autoload.php';

use server\Database;
use repo\AutomationRepository;

$database = new Database;
$repository = new AutomationRepository($database,true);

# Fetch the available for automation
// $availablForAuto = $database::fetchSql("SELECT lotNumber, workingQuantity, answerDate, bookingId FROM view_workschedule WHERE lotNumber IN (SELECT lotNumber FROM system_forautomate WHERE status NOT IN (1,2)) AND processCode IN (430,312) ORDER BY lotNumber LIMIT 1 ");
$availablForAuto = [
    // (object)['lotNumber' => '20-20-2000'],
    // (object)['lotNumber' => '20-20-10000'],
    (object)['lotNumber' => '20-20-2000'],
    // (object)['lotNumber' => '20-20-12000'],
];
foreach($availablForAuto as $auto)
{
    // $allLots = Database::fetchSql("SELECT engineering_bookingdetails.bookingId, quantity as lotQuantity, lotNumber, processName, processCode, targetFinish, targetStart FROM engineering_bookingdetails INNER JOIN view_workschedule USING (lotNumber) WHERE lotNumber LIKE '$auto->lotNumber' AND processCode IN (430,312) ORDER BY targetStart, targetFinish ASC");
    // Database::display($allLots);
    // echo '<hr>';
    // echo $auto->lotNumber.'brsd';

    //Check if it is really meant for automation 
    $repository->automateProcess($auto->lotNumber);

}






?>