<?php
include $_SERVER['DOCUMENT_ROOT'] . "/version.php";
$path = $_SERVER['DOCUMENT_ROOT'] . "/" . v . "/Common Data/";
set_include_path($path);
include("PHP Modules/mysqliConnection.php");
include('PHP Modules/gerald_functions.php');
include('PHP Modules/anthony_retrieveText.php');
include('PHP Modules/arjel_tablefunctions.php');



// function partialLotNumberTest($lot, $quantity, $startFromProcessOrder, $employeeId, $returnFlag = 0, $ngFlag = 0)
// {
//     include('PHP Modules/mysqliConnection.php');

//     $dashPosition = strrpos($lot, "-");
//     if ($dashPosition >= 9) {
//         $originalLotNumber = substr($lot, 0, $dashPosition);
//     } else {
//         $originalLotNumber = $lot;
//     }
//     // -------------------------------------------- Detect Latest Lot Number and Create New Lot Number -----------------------------------------------------------
//     $sql = "SELECT MAX( CAST(SUBSTRING(lotNumber,LOCATE('-',lotNumber,10)+1) AS SIGNED) ) as max	FROM ppic_lotlist where lotNumber like '" . $originalLotNumber . "-%'";
//     $lotQuery = $db->query($sql);
//     if ($lotQuery->num_rows > 0) {
//         $lotQueryResult = $lotQuery->fetch_assoc();
//         $newLotNumber = $originalLotNumber . "-" . ($lotQueryResult['max'] + 1);
//     } else {
//         $newLotNumber = $originalLotNumber . "-1";
//     }
//     echo "<hr>";
//     echo "<br>New lot number : {$newLotNumber}";
//     echo "<hr>Get Lot Info *****[";
//     // ----------------------------------------------------------------------------------------------------------------------------------------------------------

//     // ----------------------------------------- Insert Lot Data Into ppic_lotlist ----------------------------------------------------
//     echo $sql = "SELECT poId, partId, parentLot, partLevel, workingQuantity, identifier, status, poContentId, partialBatchId FROM ppic_lotlist where lotNumber like '" . $lot . "' AND workingQuantity > " . $quantity . " LIMIT 1";
//     echo "]<hr> 
//     Insert new lot partial in lotlist ***** [";
//     $lotQuery = $db->query($sql);
//     if ($lotQuery->num_rows > 0) {
//         $lotQueryResult = $lotQuery->fetch_assoc();

//         $newQuantity = $lotQueryResult['workingQuantity'] - $quantity;
//         # RG 02-27-20
//         if ($ngFlag == 1) $newQuantity = $quantity;

//         echo $sql = "<br>insert into ppic_lotlist (lotNumber, poId , partId, parentLot, partLevel, workingQuantity, identifier, dateGenerated, status, bookingStatus, poContentId, partialBatchId) values ('" . $newLotNumber . "', " . $lotQueryResult['poId'] . ", " . $lotQueryResult['partId'] . ", '" . $lotQueryResult['parentLot'] . "', " . $lotQueryResult['partLevel'] . ", " . $quantity . ", " . $lotQueryResult['identifier'] . ", now(), " . $lotQueryResult['status'] . ", 1, '" . $lotQueryResult['poContentId'] . "', '" . $lotQueryResult['partialBatchId'] . "')";
//         echo "]<hr> UPDATE LOT QUANTITY *****[";
//         $query = $db->query($sql);
//         // ---------------------------------------------------------------------------------------------------------------------------------

//         // --------------------------------------- Update Working Quantity of Source Lot ---------------------------------------------------
//         echo $sql = "<br>UPDATE ppic_lotlist SET workingQuantity = " . $newQuantity . " WHERE lotNumber like '" . $lot . "'";
//         echo "]<hr>";
//         $query = $db->query($sql);
//         // ------------------------------------------------------------------------------------------------------ --------------------------

//         // -------------------------------------------- Retrieve Work Schedule Data --------------------------------------------------------
//         if ($_GET['country'] == 1) {
//             $excemptedProcess = "141,174";
//         } else {
//             $excemptedProcess   = "141";
//         }
//         $sql = "select poId, customerId, poNumber, partNumber, revisionId, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag from ppic_workschedule where lotNumber like '" . $lot . "' and processCode NOT IN (" . $excemptedProcess . ") LIMIT 1";
//         $workScheduleDetailQuery = $db->query($sql);
//         $workScheduleDetailQueryResult = $workScheduleDetailQuery->fetch_assoc();
//         // -------------------------------------------- End of Retrieve Work Schedule Data --------------------------------------------------------

//         // ---------------------------------------- Insert Work Schedule Into ppic_workschedule -------------------------------------------
//         $processOrder = 1;
//         if ($_GET['country'] == 1) {
//             $excemptedProcess = "141,174,95,364,366,367,368";
//             //~ $excemptedProcess = "141,174,364,368";
//         } else {
//             $excemptedProcess   = "141,95,364,366,368";
//         }
//         echo "<hr>";
//         echo $sql = "SELECT id FROM ppic_workschedule WHERE lotNumber LIKE '" . $lot . "' AND processCode IN(95,366,367,461,597,598,599,600,601,602,603) AND status = 0 ORDER BY processOrder";
//         $queryWorkschedule = $db->query($sql);
//         if ($queryWorkschedule and $queryWorkschedule->num_rows > 0) {
//             while ($resultWorkschedule = $queryWorkschedule->fetch_assoc()) {
//                 $id = $resultWorkschedule['id'];

//                 echo $sql = " <br>
// 						INSERT INTO ppic_workschedule
// 								(	poId, customerId, poNumber, partNumber, revisionId, processCode , processSection, processRemarks, targetFinish, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag, lotNumber,				processOrder)
// 						SELECT		poId, customerId, poNumber, partNumber, revisionId, processCode , processSection, processRemarks, targetFinish, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag, '" . $newLotNumber . "',	" . ($processOrder++) . " 
// 						FROM	ppic_workschedule
// 						WHERE	id = " . $id . " LIMIT 1 
// 					";
//                 echo "<hr>";
//                 $queryInsert = $db->query($sql);
//             }
//         }
//         echo "<hr>";
//         echo $sql = "SELECT processCode, targetFinish, processSection, processRemarks FROM ppic_workschedule where lotNumber like '" . $lot . "' and processOrder>=" . $startFromProcessOrder . " AND processCode NOT IN(" . $excemptedProcess . ") ORDER BY processOrder";
//         $workScheduleQuery = $db->query($sql);
//         while ($workScheduleQueryResult = $workScheduleQuery->fetch_assoc()) {
//           echo  $sql = "<br> insert into ppic_workschedule (poId, customerId, poNumber, lotNumber, partNumber, revisionId, processCode , processOrder, processSection, processRemarks, targetFinish, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag) values (" . $workScheduleDetailQueryResult['poId'] . " ," . $workScheduleDetailQueryResult['customerId'] . ", '" . $workScheduleDetailQueryResult['poNumber'] . "' , '" . $newLotNumber . "', '" . $workScheduleDetailQueryResult['partNumber'] . "' , '" . $workScheduleDetailQueryResult['revisionId'] . "', '" . $workScheduleQueryResult['processCode'] . "' , " . ($processOrder++) . ", " . $workScheduleQueryResult['processSection'] . ", '" . $workScheduleQueryResult['processRemarks'] . "', '" . $workScheduleQueryResult['targetFinish'] . "', '" . $workScheduleDetailQueryResult['receiveDate'] . "', '" . $workScheduleDetailQueryResult['deliveryDate'] . "', '" . $workScheduleDetailQueryResult['recoveryDate'] . "', " . $workScheduleDetailQueryResult['urgentFlag'] . ", " . $workScheduleDetailQueryResult['subconFlag'] . ", " . $workScheduleDetailQueryResult['partLevelFlag'] . ")";
//             $query = $db->query($sql);
//             echo "<hr>";
//         }
//         // --------------------------------------------------------------------------------------------------------------------------------
//         // ------------------------------------------------ Insert Into PRS Log --------------------------------------------------------------------
//         echo $sql = "<br>INSERT INTO ppic_prslog (lotNumber,employeeId,date,remarks,type,sourceLotNumber,partialQuantity) values ('" . $newLotNumber . "', '" . $employeeId . "', now(), 'Automated Partial', 3,'" . $lot . "', '" . $newQuantity . "')";
//         echo "<hr>";
//         $query = $db->query($sql);
//         // -----------------------------------------------------------------------------------------------------------------------------------------

//         $sql = "SELECT id, processSection FROM `ppic_workschedule` WHERE lotNumber LIKE '" . $newLotNumber . "' AND processCode IN(312,430,431,432) ORDER BY processOrder LIMIT 1";
//         $query = $db->query($sql);
//         if ($query and $query->num_rows > 0) {
//             $result = $query->fetch_assoc();
//             $id = $result['id'];
//             $processSection = $result['processSection'];

//             $insert = "INSERT INTO `system_machineWorkschedule`(`workScheduleId`, `machineId`, `idNumber`, `sectionId`, `inputDate`, `inputTime`, `status`, `printStatus`) VALUES (" . $id . ",0,'" . $employeeId . "'," . $processSection . ",NOW(),NOW(),0,0)";
//             // $insertQuery = $db->query($insert);
//         }
//     }

//     if ($returnFlag > 0) {
//         return $newLotNumber;
//     } else {
//         return $newLotNumber = '';
//     }
// }
function partialLotNumberTest($lot, $quantity, $startFromProcessOrder, $employeeId, $returnFlag = 0, $ngFlag = 0)
{
    include('PHP Modules/mysqliConnection.php');

    $dashPosition = strrpos($lot, "-");
    if ($dashPosition >= 9) {
        $originalLotNumber = substr($lot, 0, $dashPosition);
    } else {
        $originalLotNumber = $lot;
    }

    // ---------------------------------- Detect Latest Lot Number and Create New Lot Number -----------------------------------------------------------
    $sql = "SELECT MAX( CAST(SUBSTRING(lotNumber,LOCATE('-',lotNumber,10)+1) AS SIGNED) ) as max FROM ppic_lotlist where lotNumber like '" . $originalLotNumber . "-%'";
    $lotQuery = $db->query($sql);
    if ($lotQuery->num_rows > 0) {
        $lotQueryResult = $lotQuery->fetch_assoc();
        $newLotNumber = $originalLotNumber . "-" . ($lotQueryResult['max'] + 1);
    } else {
        $newLotNumber = $originalLotNumber . "-1";
    }

    echo "<table border='1'>
            <tr><th>Step</th><th>Action</th><th>SQL Query</th></tr>";
    echo "<tr><td>1</td><td>New Lot Number Created</td><td>New lot number: {$newLotNumber}</td></tr>";

    // ----------------------------------------- Insert Lot Data Into ppic_lotlist ----------------------------------------------------
    $sql = "SELECT poId, partId, parentLot, partLevel, workingQuantity, identifier, status, poContentId, partialBatchId 
            FROM ppic_lotlist 
            WHERE lotNumber like '" . $lot . "' AND workingQuantity > " . $quantity . " LIMIT 1";
    $lotQuery = $db->query($sql);

    echo "<tr><td>2</td><td>Retrieve Lot Information</td><td>{$sql}</td></tr>";

    if ($lotQuery->num_rows > 0) {
        $lotQueryResult = $lotQuery->fetch_assoc();

        $newQuantity = $lotQueryResult['workingQuantity'] - $quantity;
        if ($ngFlag == 1) $newQuantity = $quantity;

        $sql = "INSERT INTO ppic_lotlist 
                (lotNumber, poId , partId, parentLot, partLevel, workingQuantity, identifier, dateGenerated, status, bookingStatus, poContentId, partialBatchId) 
                VALUES 
                ('" . $newLotNumber . "', " . $lotQueryResult['poId'] . ", " . $lotQueryResult['partId'] . ", '" . $lotQueryResult['parentLot'] . "', " . $lotQueryResult['partLevel'] . ", " . $quantity . ", " . $lotQueryResult['identifier'] . ", now(), " . $lotQueryResult['status'] . ", 1, '" . $lotQueryResult['poContentId'] . "', '" . $lotQueryResult['partialBatchId'] . "')";
        $db->query($sql);
       echo "<tr><td>3</td><td>Insert New Lot</td><td>{$sql}</td></tr>";

       
        $sql = "UPDATE ppic_lotlist SET workingQuantity = " . $newQuantity . " WHERE lotNumber like '" . $lot . "'";
        $db->query($sql);

        echo "<tr><td>4</td><td>Update Lot Quantity</td><td>{$sql}</td></tr>";

        if ($_GET['country'] == 1) {
            $excemptedProcess = "141,174";
        } else {
            $excemptedProcess = "141";
        }

        $sql = "SELECT poId, customerId, poNumber, partNumber, revisionId, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag 
                FROM ppic_workschedule 
                WHERE lotNumber like '" . $lot . "' 
                AND processCode NOT IN (" . $excemptedProcess . ") 
                LIMIT 1";
        $workScheduleDetailQuery = $db->query($sql);
        $workScheduleDetailQueryResult = $workScheduleDetailQuery->fetch_assoc();

        echo "<tr><td>5</td><td>Retrieve Work Schedule Data</td><td>{$sql}</td></tr>";

        // ---------------------------------------- Insert Work Schedule Into ppic_workschedule -------------------------------------------
        $processOrder = 1;
        $excemptedProcess = ($_GET['country'] == 1) ? "141,174,95,364,366,367,368" : "141,95,364,366,368";

        $sql = "SELECT id FROM ppic_workschedule 
                WHERE lotNumber LIKE '" . $lot . "' 
                AND processCode IN(95,366,367,461,597,598,599,600,601,602,603) 
                AND status = 0 
                ORDER BY processOrder";
        $queryWorkschedule = $db->query($sql);
        echo "<tr><td>6</td><td>Retrieve Process Codes for New Lot</td><td>{$sql}</td></tr>";

        if ($queryWorkschedule && $queryWorkschedule->num_rows > 0) {
            while ($resultWorkschedule = $queryWorkschedule->fetch_assoc()) {
                $id = $resultWorkschedule['id'];

                $sql = "INSERT INTO ppic_workschedule
                        (poId, customerId, poNumber, partNumber, revisionId, processCode , processSection, processRemarks, targetFinish, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag, lotNumber, processOrder)
                        SELECT poId, customerId, poNumber, partNumber, revisionId, processCode , processSection, processRemarks, targetFinish, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag, '" . $newLotNumber . "', " . ($processOrder++) . " 
                        FROM ppic_workschedule
                        WHERE id = " . $id . " LIMIT 1";
                 $db->query($sql);
                
                echo "<tr><td>7</td><td>Insert Work Schedule for Process Code</td><td>{$sql}</td></tr>";
            }
        }

        $sql = "SELECT processCode, targetFinish, processSection, processRemarks 
                FROM ppic_workschedule 
                WHERE lotNumber like '" . $lot . "' 
                AND processOrder >= " . $startFromProcessOrder . " 
                AND processCode NOT IN(" . $excemptedProcess . ") 
                ORDER BY processOrder";
        $workScheduleQuery = $db->query($sql);

        echo "<tr><td>8</td><td>Insert Remaining Work Schedule</td><td>{$sql}</td></tr>";

        while ($workScheduleQueryResult = $workScheduleQuery->fetch_assoc()) {
            $sql = "INSERT INTO ppic_workschedule 
                    (poId, customerId, poNumber, lotNumber, partNumber, revisionId, processCode , processOrder, processSection, processRemarks, targetFinish, receiveDate, deliveryDate, recoveryDate, urgentFlag, subconFlag, partLevelFlag) 
                    VALUES 
                    (" . $workScheduleDetailQueryResult['poId'] . ", " . $workScheduleDetailQueryResult['customerId'] . ", '" . $workScheduleDetailQueryResult['poNumber'] . "', '" . $newLotNumber . "', '" . $workScheduleDetailQueryResult['partNumber'] . "', '" . $workScheduleDetailQueryResult['revisionId'] . "', '" . $workScheduleQueryResult['processCode'] . "', " . ($processOrder++) . ", " . $workScheduleQueryResult['processSection'] . ", '" . $workScheduleQueryResult['processRemarks'] . "', '" . $workScheduleQueryResult['targetFinish'] . "', '" . $workScheduleDetailQueryResult['receiveDate'] . "', '" . $workScheduleDetailQueryResult['deliveryDate'] . "', '" . $workScheduleDetailQueryResult['recoveryDate'] . "', " . $workScheduleDetailQueryResult['urgentFlag'] . ", " . $workScheduleDetailQueryResult['subconFlag'] . ", " . $workScheduleDetailQueryResult['partLevelFlag'] . ")";
            $db->query($sql);

            echo "<tr><td>9</td><td>Insert into Work Schedule</td><td>{$sql}</td></tr>";
        }

        $sql = "INSERT INTO ppic_prslog 
                (lotNumber, employeeId, date, remarks, type, sourceLotNumber, partialQuantity) 
                VALUES 
                ('" . $newLotNumber . "', 'SYSTEM', now(), 'Automated Partial', 3, '" . $lot . "', '" . $newQuantity . "')";
            $db->query($sql);

        echo "<tr><td>10</td><td>Insert into PRS Log</td><td>{$sql}</td></tr>";

        $sql = "SELECT id, processSection 
                FROM `ppic_workschedule` 
                WHERE lotNumber LIKE '" . $newLotNumber . "' 
                AND processCode IN(312,430,431,432) 
                ORDER BY processOrder LIMIT 1";
        $query = $db->query($sql);

        if ($query && $query->num_rows > 0) {
            $result = $query->fetch_assoc();
            $id = $result['id'];
            $processSection = $result['processSection'];

            $insert = "INSERT INTO `system_machineWorkschedule`
                       (`workScheduleId`, `machineId`, `idNumber`, `sectionId`, `inputDate`, `inputTime`, `status`, `printStatus`) 
                       VALUES (" . $id . ", 0, '" . $employeeId . "', " . $processSection . ", NOW(), NOW(), 0, 0)";
            echo "<tr><td>11</td><td>Insert into Machine Work Schedule</td><td>{$insert}</td></tr>";
        }
    }

    echo "</table>";

    if ($returnFlag > 0) {
        return $newLotNumber;
    } else {
        return '';
    }
}


// ------------------------------- Execute When Process Was Finished (Ace) ----------------------------------
function finishProcessTest($lotNumber = "",  $currentWorkScheduleId, $workingQuantity = 0, $employeeId = '', $processRemarks = '', $currentWorkScheduleIdArray = array(), $ngQty = 0)
{
    // ------------------ Add Update Actual Start If 0000-00-00 ----------------------------------------

    include('PHP Modules/mysqliConnection.php');

    if ($_GET['country'] == "1") {
        $excemptedProcess = "141,174,95,313,366,367,364,437,438,461,597,598,599,600,601,602,603";
        $assemblyProcessArray = array(162);
    } else {
        $excemptedProcess = "141,95,313,366,367,364,438,461,597,598,599,600,601,602,603";
        $assemblyProcessArray = array(97, 162, 524, 532, 533, 534, 535, 536, 537, 538, 539, 540, 541, 542, 543, 544, 545, 546, 547, 548, 549, 550, 551, 552, 553, 555, 556);
    }


    if ($currentWorkScheduleIdArray != NULL) {
        $currentWorkScheduleId = "";
        $dateTimeNow = date("Y-m-d H:i:s");
        $dateNow = date("Y-m-d");
        foreach ($currentWorkScheduleIdArray as $workingQuantity => $value) {
            foreach ($value as $currentWorkScheduleId) {
                $sql = "SELECT customerId, lotNumber, partNumber, processCode, actualStart, processSection, processRemarks FROM ppic_workschedule WHERE id = " . $currentWorkScheduleId;
                $workScheduleQuery = $db->query($sql);
                $workScheduleQueryResult = $workScheduleQuery->fetch_array();
                $processCode = $workScheduleQueryResult['processCode'];
                $actualStart = $workScheduleQueryResult['actualStart'];
                $sectionId = $workScheduleQueryResult['processSection'];
                $partNumber = $workScheduleQueryResult['partNumber'];
                $customerId = $workScheduleQueryResult['customerId'];

                if ($lotNumber == "") {
                    $lotNumber = $workScheduleQueryResult['lotNumber'];
                }

                if ($ngQty > 0) {
                    if (count($currentWorkScheduleIdArray) > 1) {
                        $sql = "SELECT workingQuantity FROM ppic_lotlist WHERE lotNumber = '" . $workScheduleQueryResult['lotNumber'] . "'";
                        $queryLotList = $db->query($sql);
                        if ($queryLotList and $queryLotList->num_rows > 0) {
                            $resultLotList = $queryLotList->fetch_assoc();
                            $qtys = $resultLotList['workingQuantity'];

                            if ($qtys == $workingQuantity) {
                                if ($qtys == $ngQty) {
                                    $processRemarks = "Good : 0; NG : " . (floor($workingQuantity)) . ";";
                                } else {
                                    $processRemarks = "Good : " . (floor($workingQuantity)) . "; NG : 0;";
                                }
                            } else {
                                if ($qtys == $ngQty) {
                                    $processRemarks = "Good : 0; NG : " . (floor($workingQuantity)) . ";";
                                } else {
                                    $processRemarks = "Good : " . ($workingQuantity - $ngQty) . "; NG : " . $ngQty;
                                }
                            }
                        }
                    }
                }

                $actualStartParameter = "";
                if ($actualStart == "0000-00-00 00:00:00") {
                    $actualStartParameter = "actualStart = '" . $dateTimeNow . "',";
                }

                $locationComputer = isset($_COOKIE['PC']) ? $_COOKIE['PC'] : "";
                if ($locationComputer != '') {
                    if (trim($processRemarks) != "") {
                        $processRemarks .= "<br>Machine : " . $locationComputer;
                    } else {
                        $processRemarks = "Machine : " . $locationComputer;
                    }
                }

                if (in_array($processCode, array(437, 438, 461, 137, 597, 598, 599, 600, 601, 602, 603))) {
                    $sql = "UPDATE ppic_workschedule SET " . $actualStartParameter . " actualEnd='" . $dateTimeNow . "', actualFinish='" . $dateNow . "', quantity=" . ($workingQuantity) . ", employeeId='" . $employeeId . "', status=1 WHERE id = " . $currentWorkScheduleId;
                    $result = $db->query($sql);
                } else {
                    // ------------------------------------------------------------------------------------------ Finish Process --------------------------------------------------------------------------------------
                    $sql = "UPDATE ppic_workschedule SET " . $actualStartParameter . " actualEnd='" . $dateTimeNow . "', actualFinish='" . $dateNow . "', quantity=" . ($workingQuantity) . ", employeeId='" . $employeeId . "', status=1, processRemarks = '" . mysqli_real_escape_string($db, $processRemarks) . "' WHERE id = " . $currentWorkScheduleId;
                    $result = $db->query($sql);
                }

                // ----------------------------------------------------------------------------------------- Update Availability --------------------------------------------------------------------------------		
                if (!in_array($processCode, array(437, 438, 461, 597, 598, 599, 600, 601, 602, 603))) {
                    updateAvailability($lotNumber);
                }

                // ---------------------------------------------------------------------------------------- Update system_machineworkschedule ---------------------------------------------------------
                $sql = "UPDATE system_machineWorkschedule SET status = 1 WHERE inputDate = '" . $dateNow . "' AND workscheduleId = " . $currentWorkScheduleId;
                $updateQuery = $db->query($sql);

                $sql = "SELECT inputDate FROM system_machineWorkschedule WHERE inputDate > '" . $dateNow . "' AND workscheduleId = " . $currentWorkScheduleId;
                $queryCheckSched = $db->query($sql);
                if ($queryCheckSched and $queryCheckSched->num_rows > 0) {
                    $resultCheckSched = $queryCheckSched->fetch_assoc();
                    $inputDate = $resultCheckSched['inputDate'];

                    $sql = "DELETE FROM system_machineWorkschedule WHERE inputDate = '" . $inputDate . "' AND workscheduleId = " . $currentWorkScheduleId;
                    $deleteQuery = $db->query($sql);
                }

                // ---------------------------------------------------------------------------------------- Retrieve Next Process -------------------------------------------------------------------
                $sql = "SELECT id, processCode FROM ppic_workschedule WHERE status = 0 AND processCode NOT IN (" . $excemptedProcess . ") AND lotNumber = '" . $lotNumber . "' ORDER BY processOrder ASC LIMIT 1";
                $workScheduleQuery = $db->query($sql);
                $workScheduleQueryResult = $workScheduleQuery->fetch_array();
                $nextWorkScheduleId = $workScheduleQueryResult['id'];
                $nextProcessCode = $workScheduleQueryResult['processCode'];

                // ================================================================================================================================================                    

                $insertDate = date('Y-m-d');
                $insertTime = date('H:i:s');

                // $sqlKco = "SELECT kcoPartNumber FROM system_kcorevision WHERE kcoPartNumber = '".$kcoPartNumber."'";
                // $sqlKcoQuery = $db->query($sqlKco);  

                // if ($sqlKcoQuery->num_rows > 0) {
                // 	$kcoResult = $sqlKcoQuery->fetch_assoc();
                // 	$kcoPartNumber = $kcoResult['kcoPartNumber'];
                // }

                if ($customerId == 45 and $processCode == 91 and $partNumber != '') {
                    $sql = "INSERT INTO system_kcorevision (kcoPartNumber, checkFlag, insertDate, insertTime, pictureFlag) 
											VALUES ('$partNumber', '0', '$insertDate', '$insertTime', '0')";
                    $sqlQuery = $db->query($sql);
                }


                // ==============================================================================================================================================

                // ---------------------------------------------------------------------- Set Previous Process Actual Finish In Current Process --------------------------------------------------------------
                $sql = "UPDATE ppic_workschedule SET previousActualFinish='" . $dateTimeNow . "' WHERE id = " . $nextWorkScheduleId;
                $updateQuery = $db->query($sql);

                // --------------------------------------------- Remove Data In system_lotlist If Current Process Is Delivery Or Warehouse Storage ---------------------------------------------
                if ($processCode == 144 or  $processCode == 353) {
                    $sql = "DELETE FROM system_lotlist WHERE lotNumber = '" . $lotNumber . "'";
                    $deleteQuery = $db->query($sql);

                    if ($processCode == 144) {
                        $sql = "UPDATE ppic_workschedule SET status = 1 WHERE lotNumber LIKE '" . $lotNumber . "' AND processCode = 496 AND status = 0";
                        $queryUpdate = $db->query($sql);
                    }
                }
                //Rosemie 2022-05-27 Jamco KCO Final QC --start
                if (in_array($processCode, array(91))) {
                    //check if Jamco
                    $sql2Rose = "select partId, partNumber from view_workschedule where id = " . $currentWorkScheduleId . " and customerAlias LIKE '%jamco%' and partNumber like 'P%'"; //
                    $ppicQuery2Rose = $db->query($sql2Rose);
                    if ($ppicQuery2Rose->num_rows > 0) {
                        $resultParts = $ppicQuery2Rose->fetch_array();
                        $partNumber = $resultParts['partNumber'];
                        $partId = $resultParts['partId'];
                        $revisionId = $resultParts['revisionId'];
                        if ($revisionId == "") {
                            $revisionId = "NC";
                        }
                        $searchVal = "";
                        $shtValTrue = "";
                        $shtValFlag = 0;    //echo "<br>".$partNumber;
                        for ($x = (strlen($partNumber) - 1); $x >= 0; $x--) {    //echo "<br>".$partNumber[$x];
                            if ($shtValFlag == 0) {
                                if (is_numeric($partNumber[$x])) {
                                    $shtValTrue = $partNumber[$x] . "" . $shtValTrue;
                                } else {
                                    $shtValFlag = 1;
                                }
                            } else {
                                $searchVal = $partNumber[$x] . "" . $searchVal;
                            }
                        }
                        ///////------check the parts not registered at KCO checklist then register it-- START
                        $sqlmaxPart3 = "SELECT listId FROM system_kcocheck where partSearch like '" . $searchVal . "' and sheetNo like '" . $shtValTrue . "'";
                        $queryMaxParts3 = $db->query($sqlmaxPart3);
                        if ($queryMaxParts3->num_rows == 0) {
                            $sqlmaxPart3b = "INSERT INTO system_kcocheck(partSearch, sheetNo, oldHitCount, newHitCount, oldRevision, newRevision, lastUpdate) VALUES ('" . $searchVal . "','" . $shtValTrue . "','0','0','" . $revisionId . "','" . $revisionId . "',NOW())";
                            $queryMaxParts3b = $db->query($sqlmaxPart3b);        //echo "<br>sql: ".$sqlmaxPart3b;

                            $sqlmaxPart3c = "SELECT listId FROM system_kcocheck where partSearch like '" . $searchVal . "' and sheetNo like '" . $shtValTrue . "'";
                            $queryMaxParts3c = $db->query($sqlmaxPart3c);
                            if ($queryMaxParts3c->num_rows > 0) {
                                while ($resultMaxParts3c = $queryMaxParts3c->fetch_array()) {
                                    $maxpart_listId = $resultMaxParts3c['listId'];
                                }
                                $sqlmaxPart4 = "SELECT listId FROM system_kcocheckdetails where listId = '" . $maxpart_listId . "'";
                                $queryMaxParts4 = $db->query($sqlmaxPart4);
                                if ($queryMaxParts4->num_rows == 0) {
                                    $sqlmaxPart3d = "INSERT INTO system_kcocheckdetails(listId, oldPartId, newPartId, status) VALUES ('" . $maxpart_listId . "','" . $partId . "','" . $partId . "','2')";
                                    $queryMaxParts3d = $db->query($sqlmaxPart3d);        //echo "<br>sql: ".$sqlmaxPart3d;
                                }
                            }
                        } else {
                            $resultMaxParts3 = $queryMaxParts3->fetch_array();
                            $maxpart_listId3 = $resultMaxParts3['listId'];
                            //Check first if na check n siya last 24 hours
                            $today = date("Y-m-d");
                            $sqlmaxPart3 = "SELECT listId FROM system_kcocheck where listId=" . $maxpart_listId3 . " and rpaDate > '" . $today . " 00:00:00'";
                            $queryMaxParts3 = $db->query($sqlmaxPart3);
                            if ($queryMaxParts3->num_rows == 0) {
                                $sqlmaxPart5 = "SELECT listId FROM system_kcocheck where listId=" . $maxpart_listId3 . " and rpaFlag=0 and priorityFlag=0";
                                $queryMaxParts5 = $db->query($sqlmaxPart5);
                                if ($queryMaxParts5->num_rows == 0) {
                                    //select if anong priorityFlag if SALES PO INPUT //"SELECT listId FROM system_kcocheck where listId=".$maxpart_listId3." and rpaFlag=0 and priorityFlag=0// if yes dont update, no=update";
                                    if ($searchVal != "" and $shtValTrue != "") {
                                        $sql = "UPDATE system_kcocheck SET rpaFlag=0, priorityFlag=0 where partSearch like '" . $searchVal . "' and sheetNo like '" . $shtValTrue . "' LIMIT 1";
                                        $queryUpdate = $db->query($sql);
                                    }
                                }
                            }
                            //Check first if na check n siya last 24 hours								
                        }
                    }
                }
                //Rosemie 2022-05-27 Jamco KCO Final QC --end
                // -------------------------------------------- Delete Data In view_workschedule --------------------------------------------------------
                $sql = "DELETE FROM view_workschedule WHERE id = " . $currentWorkScheduleId;
                $deleteQuery = $db->query($sql);

                updateSTAnalysis($currentWorkScheduleId);

                // -------------------------------------------- Execute When Next Process Is Proceed To Assembly ------------------------------------
                if (in_array($nextProcessCode, $assemblyProcessArray)) {
                    finishProcess("",  $nextWorkScheduleId, $workingQuantity, $employeeId, $processRemarks);
                    // insertEmployeePerformance($nextWorkScheduleId);
                }

                if ($processCode == 187 and $_GET['country'] == 2) {
                    $sql = "SELECT id FROM ppic_workschedule WHERE lotNumber = '" . $lotNumber . "' AND processCode = 518 LIMIT 1";
                    $queryCheckDueDate = $db->query($sql);
                    if ($queryCheckDueDate and $queryCheckDueDate->num_rows > 0) {
                        $resultCheckDueDate = $queryCheckDueDate->fetch_assoc();
                        $workIdDue = $resultCheckDueDate['id'];

                        $sql = "UPDATE ppic_workschedule SET actualStart = '" . $dateTimeNow . "', actualEnd='" . $dateTimeNow . "', actualFinish='" . $dateNow . "', quantity=" . ($workingQuantity) . ", employeeId='" . $employeeId . "', status = 1 WHERE id = " . $workIdDue . " AND status = 0 LIMIT 1";
                        $queryUpdate = $db->query($sql);
                    }
                }

                insertEmployeePerformance($currentWorkScheduleId);
            }
        }
    } else {
        // ------------------------------------------------------------------------------------------ Retrieve Current Process (Create Function In The Future) ----------------------------------------------------------------------
        $dateTimeNow = date("Y-m-d H:i:s");
        echo $sql = "SELECT lotNumber, processCode, actualStart, processSection FROM ppic_workschedule WHERE id = " . $currentWorkScheduleId;
        echo "<br>";
        $workScheduleQuery = $db->query($sql);
        if ($workScheduleQuery and $workScheduleQuery->num_rows > 0) {
            $workScheduleQueryResult = $workScheduleQuery->fetch_array();
            $processCode = $workScheduleQueryResult['processCode'];
            $actualStart = $workScheduleQueryResult['actualStart'];
            $sectionId = $workScheduleQueryResult['processSection'];

            if ($lotNumber == "") {
                $lotNumber = $workScheduleQueryResult['lotNumber'];
            }

            $actualStartParameter = "";
            if ($actualStart == "0000-00-00 00:00:00") {
                $actualStartParameter = "actualStart = now(),";
            }

            $locationComputer = isset($_COOKIE['PC']) ? $_COOKIE['PC'] : "";
            if ($locationComputer != '') {
                if (trim($processRemarks) != "") {
                    $processRemarks .= "<br>Machine : " . $locationComputer;
                } else {
                    $processRemarks = "Machine : " . $locationComputer;
                }
            }

            if (in_array($processCode, array(437, 438, 461, 137, 138, 229, 597, 598, 599, 600, 601, 602, 603))) {
                echo   $sql = "UPDATE ppic_workschedule SET " . $actualStartParameter . " actualEnd='" . $dateTimeNow . "', actualFinish=now(), quantity=" . $workingQuantity . ", employeeId='" . $employeeId . "', status=1 WHERE id = " . $currentWorkScheduleId;
                $result = $db->query($sql);
                echo "<br>";

            } else {
                // ------------------------------------------------------------------------------------------ Finish Process --------------------------------------------------------------------------------------
                echo   $sql = "UPDATE ppic_workschedule SET " . $actualStartParameter . " actualEnd='" . $dateTimeNow . "', actualFinish=now(), quantity=" . $workingQuantity . ", employeeId='" . $employeeId . "', status=1, processRemarks = '" . mysqli_real_escape_string($db, $processRemarks) . "' WHERE id = " . $currentWorkScheduleId;
                $result = $db->query($sql);
                echo "<br>";

            }

            // ----------------------------------------------------------------------------------------- Update Availability --------------------------------------------------------------------------------		
            if (!in_array($processCode, array(437, 438, 461, 597, 598, 599, 600, 601, 602, 603))) {
                updateAvailability($lotNumber);
            }

            // ---------------------------------------------------------------------------------------- Update system_machineworkschedule ---------------------------------------------------------
            echo $sql = "UPDATE system_machineWorkschedule SET status = 1 WHERE inputDate = '" . date("Y-m-d") . "' AND workscheduleId = " . $currentWorkScheduleId;
            $updateQuery = $db->query($sql);
            echo "<br>";


            echo  $sql = "SELECT inputDate FROM system_machineWorkschedule WHERE inputDate > '" . date("Y-m-d") . "' AND workscheduleId = " . $currentWorkScheduleId;
            $queryCheckSched = $db->query($sql);
            echo "<br>";

            if ($queryCheckSched and $queryCheckSched->num_rows > 0) {
                $resultCheckSched = $queryCheckSched->fetch_assoc();
                $inputDate = $resultCheckSched['inputDate'];

                echo  $sql = "DELETE FROM system_machineWorkschedule WHERE inputDate = '" . $inputDate . "' AND workscheduleId = " . $currentWorkScheduleId;
                $deleteQuery = $db->query($sql);
                echo "<br>";

            }

            // ---------------------------------------------------------------------------------------- Retrieve Next Process -------------------------------------------------------------------
            echo $sql = "SELECT id, processCode FROM ppic_workschedule WHERE status = 0 AND processCode NOT IN (" . $excemptedProcess . ") AND lotNumber = '" . $lotNumber . "' ORDER BY processOrder ASC LIMIT 1";
            $workScheduleQuery = $db->query($sql);
            $workScheduleQueryResult = $workScheduleQuery->fetch_array();
            $nextWorkScheduleId = $workScheduleQueryResult['id'];
            $nextProcessCode = $workScheduleQueryResult['processCode'];
            echo "<br>";


            // ---------------------------------------------------------------------- Set Previous Process Actual Finish In Current Process --------------------------------------------------------------
            echo $sql = "UPDATE ppic_workschedule SET previousActualFinish=now() WHERE id = " . $nextWorkScheduleId;
            echo "<br>";

            $updateQuery = $db->query($sql);

            // --------------------------------------------- Remove Data In system_lotlist If Current Process Is Delivery Or Warehouse Storage ---------------------------------------------
            if ($processCode == 144 or  $processCode == 353) {
                echo  $sql = "DELETE FROM system_lotlist WHERE lotNumber = '" . $lotNumber . "'";
                echo "<br>";

                // $deleteQuery = $db->query($sql);

                if ($processCode == 144) {
                    echo $sql = "UPDATE ppic_workschedule SET status = 1 WHERE lotNumber LIKE '" . $lotNumber . "' AND processCode = 496 AND status = 0";
                    $queryUpdate = $db->query($sql);
                    echo "<br>";
                    
                }
            }

            // -------------------------------------------- Delete Data In view_workschedule --------------------------------------------------------
            echo $sql = "DELETE FROM view_workschedule WHERE id = " . $currentWorkScheduleId;
            echo "<br>";

            $deleteQuery =   $db->query($sql);

            // updateSTAnalysis($currentWorkScheduleId);

            // -------------------------------------------- Execute When Next Process Is Proceed To Assembly ------------------------------------
            if (in_array($nextProcessCode, $assemblyProcessArray)) {
                // finishProcess("",  $nextWorkScheduleId, $workingQuantity, $employeeId, $processRemarks);    
                // insertEmployeePerformance($nextWorkScheduleId);
            }

            if ($processCode == 187 and $_GET['country'] == 2) {
                echo $sql = "SELECT id FROM ppic_workschedule WHERE lotNumber = '" . $lotNumber . "' AND processCode = 518 LIMIT 1";
                echo "<br>";

                $queryCheckDueDate = $db->query($sql);
                if ($queryCheckDueDate and $queryCheckDueDate->num_rows > 0) {
                    $resultCheckDueDate = $queryCheckDueDate->fetch_assoc();
                    $workIdDue = $resultCheckDueDate['id'];

                    echo $sql = "UPDATE ppic_workschedule SET actualStart = '" . $dateTimeNow . "', actualEnd='" . $dateTimeNow . "', actualFinish='" . $dateNow . "', quantity=" . ($workingQuantity) . ", employeeId='" . $employeeId . "', status = 1 WHERE id = " . $workIdDue . " AND status = 0 LIMIT 1";
                    $queryUpdate = $db->query($sql);
                    echo "<br>";
                }
            }

            // insertEmployeePerformance($currentWorkScheduleId);

            /* Commented On October 4 Because Top 20 Is No Longer Required
                if(in_array($sectionId,array(1,2)))
                {			
                    fillGroupSchedule($sectionId);			
                }
                */
        }
    }
}
