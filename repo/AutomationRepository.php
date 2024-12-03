<?php 
namespace repo;

use DateTime;
use model\Automation;
use server\Database;
use server\Notification\Observer;
use server\Notification\Subject;
use stdClass;

class AutomationRepository
{
    protected $database;
    protected $testMode;
    private $currentDataLot= [];
    public function __construct(Database $database, $testMode = false)
    {
        $this->database = $database;
        define("PROGRAM_PATH", $_SERVER['DOCUMENT_ROOT']. '/Document Management System/Automation Nesting Folder/Program');
        define("NESTING_PATH", $_SERVER['DOCUMENT_ROOT']. '/Document Management System/Automation Nesting Folder/Drawing');   
        define("TESTING_PATH", $_SERVER['DOCUMENT_ROOT']. '/Document Management System/Automation Nesting Folder/Testing');   
        define("ACTUAL_PROGRAM_PATH", $_SERVER['DOCUMENT_ROOT']. '/Document Management System/Nesting Program Folder');   
        define("ACTUAL_DRAWING_PATH", $_SERVER['DOCUMENT_ROOT']. '/Document Management System/Nesting Drawing Folder');   

        $this->testMode = $testMode;
    }

    public function fetchAutomations(array $config)
    {
        // Unpack the config parameters
        list($length, $start, $draw, $sql, $totalRecords) = $config;
        $sql .= " LIMIT $start, $length";
        $data = $this->database::fetchSql($sql);
        $response = array_map(function($automate) use (&$start){
            $actionButton = "
                <span class='w3-center'>
                    <button class='btn btn-primary btn-sm' 
                            style='font-size: .7rem !important;' 
                            onclick='viewTaskDetails($automate->autoId)'>
                        See details
                    </button>
                 </span>";

            return [
                ++$start,
                $automate->partNumber,
                $automate->drawing ? $automate->drawing : "No drawing",
                $automate->program ? $automate->program : "No program",
                $automate->sheetQuantity,
                $automate->cuttingCondition,
                $automate->materialName,
                $automate->materialThickness,
                $automate->materialHeight." X ".$automate->materialWidth,
                $automate->automator,
                $actionButton
            ];
        },$data);
        // Return the response in JSON format
        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => intval($totalRecords),
            'recordsFiltered' => intval($totalRecords),
            'data' => $response, // Use the prepared response data
        ]);
    }

    public function create($params){

        $auto = new Automation();

        foreach ($params as $key => $value) {
            // Construct the setter method name based on the property name
            $setterMethod = 'set' . ucfirst($key);
            // echo $setterMethod;
            if (method_exists($auto, $setterMethod)) {
                $auto->$setterMethod($value);
            }
        }
        // Step 1: Gather data for insertion, excluding file upload fields
        $filterData = $this->filterDataForDatabase($auto);

        // Step 2: Prepare SQL statement for insertion
        $automator = $_SESSION['idNumber'];
        $sql = $this->prepareInsertSQL($filterData, $automator);

        // Step 3: Insert into the database and handle file uploads if successful
        $conn = $this->database->getDB();
        if ($this->executeInsert($conn, $sql)) {
            $autoId = $conn->insert_id;
            $this->handleFileUploads($auto, $autoId, $filterData['partNumber']);
            header("location: index.php?alert=success");
            exit; // Ensure no further code is executed after the redirect
        } else {
            throw new \Exception("Database Insert Error: " . $conn->error);
        }
    }

    private function executeInsert($conn, $sql)
    {
        // echo $sql;
        return $conn->query($sql);
    }

    private function filterDataForDatabase(Automation $auto)
    {
        // Here, you'd extract the properties from the Automation model and filter them as necessary
        $data = [
            'partNumber' => $auto->getPartNumber(),
            'materialName' => $auto->getMaterialName(),
            'sheetQuantity' => $auto->getsheetQuantity(),
            'materialThickness' => $auto->getmaterialThickness(),
            'materialHeight' => $auto->getmaterialHeight(),
            'materialWidth' => $auto->getMaterialWidth(),
            'cuttingCondition' => $auto->getCuttingCondition(),
            'machine' => $auto->getMachine(),
            'processTime' => $auto->getProcessTime(),
            'automator' => $_SESSION['idNumber'],
        ];

        return array_filter($data);
    }

    private function prepareInsertSQL(array $filterData, $automator)
    {
        $fields = implode(", ", array_keys($filterData));
        $values = "'" . implode("', '", array_values($filterData)) . "'";

        return "INSERT INTO system_automation ($fields) VALUES ($values)";
    }

    private function handleFileUploads(Automation $auto, $autoId, $partNumber)
    {
        if ($auto->getNestingDrawing()) {
            $this->onFileUpload($auto->getNestingDrawing(), NESTING_PATH, $autoId, $partNumber);
        }

        if ($auto->getNestingDrawing()) {
            $this->onFileUpload($auto->getNestingProgram(), PROGRAM_PATH, $autoId, $partNumber);
        }
    }

    private function onFileUpload(array $fileData, string $path, $id, $partNumber)
    {
        // Ensure the directory exists
        if (!is_dir($path)) {
            mkdir($path, 0777, true); // Create the directory if it doesn't exist
        }

        $fileName = $fileData['name']; // Original name
        $fileTempPath = $fileData['tmp_name']; // Temporary file path
        $fileError = $fileData['error']; // Error code

        // Define the file path for the upload
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $filePath = "$path/{$partNumber}_{$id}.$fileExtension";

        // Check for errors
        if (!$fileError === UPLOAD_ERR_OK) throw new \Exception("Error uploading file $fileName. Error code: $fileError.");
        if (move_uploaded_file($fileTempPath, $filePath)) {
            return true;
        } else {
            throw new \Exception("File upload failed for $fileName.");
        }
    }

    public function automateProcess($lotNumber)
    {
        //get details; 
        $selectedAuto = $this->setupBooking($lotNumber);
        // foreach ($selectedAuto as $auto) {
        //     // $this->runAutomation($auto->lotNumber);
        //     // break;
        //     // $this->runAutomation($lotNumber);
        // }
        $this->runAutomation($selectedAuto);
        Database::display($selectedAuto);
    }

    private function setupBooking(string $lot): array
    {
        $fetchAllLots = Database::fetchSql("SELECT lotNumber FROM engineering_bookingdetailsJera WHERE bookingId =  (SELECT bookingId FROM engineering_bookingdetailsJera WHERE lotNumber LIKE '$lot')");
        $allLots = implode("','", array_column($fetchAllLots, 'lotNumber'));
        $bookingLots = Database::fetchSql("SELECT details.bookingId, partNumber, quantity as lotQuantity, materialRequirement, lotNumber, processName, processCode, targetFinish, targetStart FROM engineering_bookingdetailsJera as details INNER JOIN view_workschedule USING (lotNumber) WHERE lotNumber IN ('$allLots') AND processCode IN (430,312) ORDER BY targetStart, targetFinish ASC");
        $data = $this->categorizeAutomation($bookingLots);
        // Database::display($data);
        return $data ?? [];
    }

    private function categorizeAutomation(array $bookingData)
    {
        $today = new DateTime();

        $nearestDate = array_reduce($bookingData, function ($nearest, $entry) use ($today) {
            $startDate = new DateTime($entry->targetStart);
            if ($startDate->format('Y-m-d') === '0000-00-00') return $nearest;

            $diff = abs($today->diff($startDate)->days);
            if ($diff > 7) return $nearest; // Skip dates with a diff greater than 7

            return $nearest === null || $diff < $nearest['diff'] ? ['date' => $startDate, 'diff' => $diff] : $nearest;
        }, null)['date'] ?? null;

        if (!$nearestDate) {
            return [];
        }

        // Step 2: Filter dates within 5 days of the nearest date
        $filtered = array_filter($bookingData, function ($entry) use ($nearestDate) {
            $startDate = new DateTime($entry->targetStart);
            return $nearestDate && abs($nearestDate->diff($startDate)->days) <= 5;
        });

        // Step 3: Check and sum quantities until max is reached
        $selected = [];
        $totalQuantity = 0;

        foreach ($filtered as $entry) {
            $maxOfPart = $this->getMaxOfParts($entry->partNumber) ;
            $maxQuantity = $maxOfPart->maxQuantity ?? 0;

            // Database::display($maxOfPart);

            if ($maxQuantity<1) continue; // continue when max is not set in database
            if ($totalQuantity >= $maxQuantity) break; // break if equal

            if ($entry->lotQuantity + $totalQuantity > $maxQuantity) {
                // Adjust the lot quantity to match the remaining needed
                // $entry->lotQuantity = $maxQuantity - $totalQuantity;
                $totalQuantity = $maxQuantity;
            } else {
                $totalQuantity += $entry->lotQuantity;
            }

            $selected[] = $entry;
        }

        // Display the selected entries
        // Database::display($selected);

        return $selected;
    }

    private function getMaxOfParts($partNumber)
    {
        return Database::fetchSql("SELECT maxQuantity FROM system_automationparts WHERE partNumber LIKE '$partNumber'AND status = 0 LIMIT 1")[0];
    }

    private function runAutomation(array $data)
    {

        $automation = new Automation();
        $_lotNumbers = array_column($data, 'lotNumber');
        $_lotQuantity = array_column($data, 'lotQuantity');
        $fillerArray = array_combine($_lotNumbers, $_lotQuantity);
        Database::display($data);
        $details = $this->automationDetails($data[0]->lotNumber);
        $isExisted = $this->hasExistingFile($details->partNumber);
        $partCapacity = $this->getMaxOfParts($details->partNumber);
        $check = true;
        if (reset($fillerArray) >= $partCapacity->maxQuantity)
        {
            echo "excesss";
            Database::display(reset($fillerArray));
            if(((int)reset($fillerArray) == (int)$partCapacity->maxQuantity))
            {
                $check = false;
                $fillerArray = [];
            }
        }

        $automation->setFiller($fillerArray);
        $automation->setHasExcess($check);

        // Database::display($automation);

        $nestingProcesses = [
            2 => 430,
            1 => 312
        ];

        if ($isExisted && $details) {
            if ($partCapacity->maxQuantity) {
                if(count($automation->getFiller()) > 1)  $details->workingQuantity = array_sum($automation->getFiller());
        
                $sheetQuantity = ceil($details->workingQuantity / $details->quantityPerSheet);
                $divisor = floor($details->workingQuantity / $partCapacity->maxQuantity);

                $automation->setLotNumber($details->lotNumber)
                    ->setLaserNestingProcess($nestingProcesses[$details->nestingType])
                    ->setAutoId($details->autoId)
                    ->setBookingId($details->bookingId)
                    ->setPartNumber($details->partNumber)
                    ->setNestingDrawing(['path' => NESTING_PATH . "/{$details->partNumber}_{$details->autoId}.pdf"])
                    ->setNestingProgram(['path' => PROGRAM_PATH . "/{$details->partNumber}_{$details->autoId}.001"])
                    ->setMaterialName($details->materialName)
                    ->setMaterialThickness($details->thickNess)
                    ->setMaterialWidth($details->width)
                    ->setMaterialHeight($details->height)
                    ->setSheetQuantity($sheetQuantity)
                    ->setWorkingQuantity($details->workingQuantity)
                    ->setQuantityOfSheet($details->quantityPerSheet);
                // Database::display($automation);
                $this->processDividedAutomation($automation, $partCapacity, $divisor);
            }
        }


        // // Create Notificiation here
        // if (!$isExisted) {
        //     $this->updateWorkingTable($lotNumber, 3); // 3 means no data, engineer must be notified and input data

        //     $subject = new Subject();
        //     $subject->attach(new Observer('1163'));
        //     $subject->attach(new Observer('0939'));
        //     $message = [
        //         'notificationDetail' => "Attention : Please input automation details for {$lotNumber} to automate nesting",
        //         'notificationKey' => $lotNumber,
        //         'notificationLink' => "/V4/Others/Ericj/Auto%20Finish%20Booking/index.php?route=fetch/notif&",
        //         'notificationType' => 13
        //     ];
        //     $subject->notify($message);
        // }
    }

    private function processDividedAutomation(Automation $automation, $partCapacity, $divisor)
    {   

        //setup fllers to become more dynamic
        $_getFillers = $automation->getFiller();
        $orginalQuantity =  (count($_getFillers) > 0) ? end($_getFillers) :  $automation->getWorkingQuantity();
        $originLot = (count($_getFillers) > 0) ? key($_getFillers) :$automation->getLotNumber();

        if(count($_getFillers)> 1) $automation->setWorkingQuantity($orginalQuantity); // dynamic get of working quantitybot filler and single
        array_pop($_getFillers); //preparation for multi lot and filler
        $dataConfigBoooking = [
            'quantityPerSheet' => $automation->getQuantityOfSheet(),
            'orginalQuantity' => $orginalQuantity,
            'bookingId' => $automation->getBookingId(),
            'lotNumber' => $originLot,
            'filler' => $_getFillers,
            'excess' => $automation->getHasExcess()
        ];
        $automation->getLotNumber();
        for ($i = 0; $i < $divisor; $i++) {
            echo "{$automation->getLotNumber()} <br>";
            
            echo $totalCoveredAutomation = (count($_getFillers) > 0) ? $partCapacity->maxQuantity - (array_sum($_getFillers))  : $partCapacity->maxQuantity;
            $diff = abs($totalCoveredAutomation - $automation->getWorkingQuantity());
            $totalWorkingQuantity = $totalCoveredAutomation + $diff;
            echo "Total Covered For Automation: {$totalCoveredAutomation} || Partial: {$diff} || Total: {$totalWorkingQuantity} <br>";

            if($diff + $totalCoveredAutomation == $totalWorkingQuantity)
            {
                $newLotNumber = ($automation->getHasExcess() ==false)? $originLot:$this->removeExcessQuantity($originLot, $totalCoveredAutomation);
                $automation->setLotNumber($newLotNumber);
                echo "orgin lotNumber : {$originLot} <br>";
                echo "Equal New lotNumber : {$newLotNumber} <br>";
                echo "quantitypersheet : {$automation->getQuantityOfSheet()} <br>";
                echo "automationId : {$automation->getAutoId()} <br>";

                $dataConfigBoooking[ 'totalCovered'] = $totalCoveredAutomation;
                $dataConfigBoooking['newLotNumber'] = $newLotNumber;
                $dataConfigBoooking['diff'] = $diff;
                if((count($_getFillers) > 0))
                {
                    $dataConfigBoooking['filler'][$newLotNumber] =  $totalCoveredAutomation;
                    if ($diff > 0) {
                        $filler_array = $automation->getFiller();
                        unset($filler_array[$originLot]);
                        Database::display($filler_array);
                        $automation->setFiller($filler_array);
                    } 
                    else $dataConfigBoooking['excess'] = false;
                  
                    $totalCoveredAutomation = array_sum($dataConfigBoooking['filler']);
                }
                else  $dataConfigBoooking['filler'] = [];

                $sheetQuantity = $totalCoveredAutomation / $automation->getQuantityOfSheet();
                $newAutoId = $this->getProgramDrawingBySheet($sheetQuantity, $automation->getAutoId());

                echo "sheetqt : {$sheetQuantity} <br>";

                if($newAutoId) 
                {
                    $automation
                    ->setNestingDrawing(['path' => NESTING_PATH . "/{$automation->getPartNumber()}_{$newAutoId}.pdf"])
                    ->setNestingProgram(['path' => PROGRAM_PATH . "/{$automation->getPartNumber()}_{$newAutoId}.001"]);
                }

                $newBookingId = $this->putOnNewBooking($dataConfigBoooking);
                if ($newBookingId) $automation->setBookingId($newBookingId);
                $this->autoFinishBooking($automation);
           
            }   

            $automation->setWorkingQuantity($diff);
          

            if ($diff < $partCapacity->maxQuantity) {
                $newDiff = floor($diff / $automation->getQuantityOfSheet());

                if ($newDiff >= 1) {
                    $excessQuantity = $newDiff * $automation->getQuantityOfSheet();
                    $partialQuantity = abs($diff - $excessQuantity);

                    $newLotNumber =$partialQuantity > 0 ?  $this->removeExcessQuantity($originLot, $excessQuantity) : $originLot;
                    $automation->setLotNumber($newLotNumber);
                    $automation->setWorkingQuantity($partialQuantity);
                    echo "orgin lotNumber : {$originLot} <br>";
                    echo "Total Covered For Automation: {$excessQuantity} || Partial: {$partialQuantity} || Total: {$totalWorkingQuantity} <br>";

                    $dataConfigBoooking['totalCovered'] = $excessQuantity;
                    $dataConfigBoooking['newLotNumber'] = $newLotNumber;
                    $dataConfigBoooking['diff'] = $partialQuantity;
                    $dataConfigBoooking['filler'] = [];

                    

                    $sheetQuantity = $excessQuantity / $automation->getQuantityOfSheet();
                    echo "sheetqt : {$sheetQuantity} <br>";

                    if ($sheetQuantity) {
                        $newAutoId = $this->getProgramDrawingBySheet($sheetQuantity, $automation->getAutoId());
                        $automation
                        ->setNestingDrawing(['path' => NESTING_PATH . "/{$automation->getPartNumber()}_{$newAutoId}.pdf"])
                        ->setNestingProgram(['path' => PROGRAM_PATH . "/{$automation->getPartNumber()}_{$newAutoId}.001"]);
                    }

                    if($partialQuantity > 0)
                    {
                        $filler_array = $automation->getFiller();
                        unset($filler_array[$originLot]);
                        Database::display($filler_array);
                        $automation->setFiller($filler_array);
                    }           
                    else  $dataConfigBoooking['excess'] = false; // false if has no more partial quantity    

                    $newBookingId = $this->putOnNewBooking($dataConfigBoooking);
                    if($newBookingId) $automation->setBookingId($newBookingId);
                    $this->autoFinishBooking($automation);
                }
            }
        }
    }


    private function putOnNewBooking(array $data)
    {
        //'totalCovered' => $totalCoveredAutomation,
        //'bookingId' => $automation->getBookingId,
        //'lotNumber' => $originLot,
        //'newLotNumber' => $newLotNumber,
        //'quantityPerSheet' => $automation->getQuantityOfSheet(),
        //'orginalQuantity' => $orginalQuantity,
        // 'diff' => $partialQuantity,
        // 'filler' => $partialQuantity,
        // extract the data 
        
        extract($data);

        Database::display($data);

        $dataBooking = Database::fetchSql("SELECT * FROM engineering_bookingTest WHERE bookingId = $bookingId");
        $originalBookingQuantity = $dataBooking[0]->bookingQuantity;

        // Prepare booking data to insert
        $bookingDataToInsert = array_map(function ($item)use ($data) {

            $item->bookingQuantity = (count($data['filler']) > 1 ? array_sum($data['filler']) :  $data['totalCovered']) / $data['quantityPerSheet'];
            $item->bookingId = ''; // Reset bookingId for the new booking   

            return (array)$item; // Convert object to array
        }, $dataBooking);

        Database::display($bookingDataToInsert);


        if ($bookingDataToInsert) {
            // Insert the new booking
            $this->database->table("engineering_bookingTest")
                ->setValues($bookingDataToInsert[0])
                ->execute("insert");

            $lastId = $this->database->lastInsertId();

            if ($lastId && $bookingId) {
                //insert to new booking
                if(count($filler) > 1)
                {
                  foreach($filler as $key => $val)
                  {
                    if($key)
                    {
                        echo "<br>current lot: {$key} - current quantity = {$val} <br>";
                        $this->database->table('engineering_bookingdetailsJera')
                            ->setValues([
                                'quantity' => $val,
                                'bookingId' => $lastId
                            ])
                            ->where('lotNumber', $key)
                            ->limit(1)
                            ->execute('update');
                    }
                  }
                }

                if ($excess == true) {
                    $this->database->table('engineering_bookingdetailsJera')
                    ->setValues([
                        'lotNumber' => $newLotNumber,
                        'quantity'  => $totalCovered,
                        'bookingId' => $lastId,
                    ])
                    ->execute('insert');
                }
                else
                {
                    if($lotNumber)
                    {
                        $this->database->table('engineering_bookingdetailsJera')
                            ->setValues([
                                'bookingId' => $lastId
                            ])
                            ->setStrict(true)
                            ->where('lotNumber', $lotNumber)
                            ->limit(1)
                            ->execute('update');
                    }
                   
                }
                    
                if($diff)
                {
                    if($lotNumber)
                    {
                        $this->database->table('engineering_bookingdetailsJera')
                            ->setValues(['quantity' => $diff])
                            ->setStrict(true)
                            ->where('lotNumber', $lotNumber)
                            ->limit(1)
                            ->execute('update');
                    }
                }
           
                // Calculate the new booking quantity for the original booking
                if($bookingId){
                    $newBookingQuantity = abs($originalBookingQuantity - $bookingDataToInsert[0]['bookingQuantity']);
                     $this->database->table("engineering_bookingTest")
                        ->setValues(['bookingQuantity' => $newBookingQuantity])
                        ->setStrict(true)
                        ->where("bookingId", $bookingId)
                        ->limit(1)
                        ->execute('update');
                }
                return $lastId;
            }
        }
    }

    private function getProgramDrawingBySheet(int $sheetQuantity, int $basedId) 
    {
        $base = Database::fetchSql("SELECT * FROM system_automation WHERE autoId = $basedId")[0];
        $main = Database::fetchSql("SELECT autoId FROM system_automation WHERE sheetQuantity = $sheetQuantity AND partNumber LIKE '$base->partNumber' AND materialThickness = '$base->materialThickness'")[0];
        echo "SELECT * FROM system_automation WHERE autoId = $basedId <br>"; 
        echo "SELECT autoId FROM system_automation WHERE sheetQuantity = $sheetQuantity AND partNumber LIKE '$base->partNumber' AND materialThickness = '$base->materialThickness' <br>"; 
        echo $main->autoId."<br>";
        return $main->autoId ? $main->autoId :  0;
    }

    private function automationDetails($lotNumber)
    {
        $booking = Database::fetchSql("SELECT bookingId,quantity FROM engineering_bookingdetailsJera WHERE lotNumber LIKE '$lotNumber' LIMIT 1")[0];
        $testModeData = (object)[
            'lotNumber' => $lotNumber,
            'bookingId' => $booking->bookingId, 
            'autoId' => 78, 
            'partNumber' => 'PV00977A M92',
            'materialName' => 'JFE-H400-ECOG',
            'thickNess' => '3.200',
            'width' => '1000.000',
            'height' => '1160.000',
            'quantityPerSheet' => '24',
            'nestingType' => 2,
            // 'sheetQuantity' => '10',
            'materialRequirement' => '17',
            'workingQuantity' => $booking->quantity, 
        ];
        return $this->testMode? $testModeData:Database::fetchSql("
              SELECT 
                    lotNumber, 
                    partId, 
                    parts.partNumber, 
                    workingQuantity, 
                    sheetQuantity, 
                    book.bookingId,
                    book.bookingStatus,
                    book.nestingType,
                    autoId,
                    system_automation.quantityPerSheet,
                    (workingQuantity / system_automation.quantityPerSheet ) as materialRequirement
                FROM 
                    ppic_lotlist
                INNER JOIN 
                    cadcam_parts as parts USING (partId)
                INNER JOIN 
                    engineering_bookingdetailsJera USING (lotNumber)
                INNER JOIN 
                    engineering_bookingTest as book USING (bookingId) 
               LEFT JOIN 
                    system_automation ON system_automation.partNumber = parts.partNumber 
                 WHERE lotNumber LIKE '$lotNumber' AND book.bookingStatus = 2 AND (workingQuantity / system_automation.quantityPerSheet) >= system_automation.sheetQuantity
                ORDER BY sheetQuantity DESC
            ")[0];
    }

    private function hasExistingFile($partNumber): bool
    {
   
        // Use glob to find matching files
        $pdfFiles = glob(NESTING_PATH."/{$partNumber}_*.pdf");
        $zipFiles = glob(PROGRAM_PATH."/{$partNumber}_*.001");
        // Check if any matching files were found in both directories
        return !empty($pdfFiles) && !empty($zipFiles);
    }

    private function removeExcessQuantity($lotNumber, $diff) :string
    {
       $newLotNumber = partialLotNumberTest($lotNumber, $diff, 1,'', 1);
       return $newLotNumber ?? '';
    }

    private function autoFinishBooking(Automation $automation)
    {
        Database::display($automation);
        $lotNumber = $automation->getLotNumber();
        $processCode = $automation->getLaserNestingProcess();
        $this->transferAutoToMainFolder([
           ACTUAL_DRAWING_PATH => [ $automation->getBookingId(), $automation->getNestingDrawing()['path'] ],
           ACTUAL_PROGRAM_PATH =>  [ $automation->getBookingId(), $automation->getNestingProgram()['path']]
        ],$automation->getHasExcess(),true);

        $lotsToFinish = array_merge([$lotNumber], array_keys($automation->getFiller())); // Add $lotNumber to the array
        Database::display(array_keys($lotsToFinish));

        // Safely implode for SQL query
        $lotValues = implode("','", $lotsToFinish);
        echo "<br>SELECT id FROM view_workschedule WHERE lotNumber IN ('$lotValues') AND processCode = $processCode";
        $workScheduleIdArray = Database::fetchSql("SELECT id FROM view_workschedule WHERE lotNumber IN ('$lotValues') AND processCode = $processCode");

        foreach ($workScheduleIdArray as $workScheduleId) {
            if ($workScheduleId) {
                $this->finishCurrentAutomation($workScheduleId->id, $lotNumber);
            }
        }
    }

    private function transferAutoToMainFolder(array $paths,$type, $testMode = false)
    {
        // $destination = $testMode == t ? TESTING_PATH : ACTUAL_PROGRAM_PATH;
        foreach($paths as $dir => $data)
        {
            $destination = $testMode == true ? TESTING_PATH : $dir;
            // Ensure the destination includes the new filename
            list($bookingId, $path) = $data;
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $suffix = $type ? "-1" : "";
            $destination = $destination . DIRECTORY_SEPARATOR . $bookingId."$suffix.$extension";
            echo copy($path, $destination) ? "nicee path has been move to". $destination."<br>" : "oh no error in path".$path." <br> ";
        }
    }

    private function finishCurrentAutomation($id, $lotNumber) 
    {
        finishProcessTest($lotNumber, $id, 0, '', 'Automations Nesting done by PMS');
        $this->updateWorkingTable($lotNumber, 1); // 1 meaning success
    }

    private function updateWorkingTable(string $lotNumber, int $status)
    {
        $update = new Database;
        $update->table('system_forautomate')
            ->setStrict(true)
            ->setValues(['status' => $status])
            ->where('lotNumber', $lotNumber)
            ->execute('update');
        // echo $update->getGeneratedQuery();
    }

    public function notificationUpdate($param)
    {   
        extract($param);
        if($notificationId) $this->database->table('system_notification')->setValues(['notificationStatus' => 1])->where('notificationId', $notificationId)->execute('update');
    }
}
