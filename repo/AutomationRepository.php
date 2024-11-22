<?php 
namespace repo;

use model\Automation;
use server\Database;
use server\Notification\Observer;
use server\Notification\Subject;

class AutomationRepository
{
    protected $database;
    protected $testMode;
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
        $details = $this->automationDetails($lotNumber);
        $isExisted = $this->hasExistingFile($details->partNumber);
        echo "<pre>{$lotNumber}" . print_r($details, true) . "</pre>";

        $nestingProcesses = [
            1 => 430,
            2 => 312
        ];

        if($isExisted AND $details)
        {
            $automation = new Automation();
            $automation->setLotNumber($details->lotNumber)
                    ->setLaserNestingProcess($nestingProcesses[$details->nestingType])
                    ->setBookingId($details->bookingId)
                    ->setPartNumber($details->partNumber)
                    ->setsheetQuantity($details->sheetQuantity)
                    ->setNestingDrawing(['path'=>NESTING_PATH."/{$details->partNumber}_{$details->autoId}.pdf"]) // this should be array with key
                    ->setNestingProgram(['path'=>PROGRAM_PATH."/{$details->partNumber}_{$details->autoId}.zip"])  // this should be array
                    ->setMaterialName($details->materialName)
                    ->setmaterialThickness($details->thickNess)
                    ->setmaterialWidth($details->width)
                    ->setMaterialHeight($details->height)
                    ->setQuantityOfSheet($details->quantityPerSheet);       

            $flag = false;
            if ($details->sheetQuantity < $details->materialRequirement) 
            {
                $flag = true;
                $totalCoveredAutomation = $automation->getQuantityOfSheet() * $automation->getsheetQuantity();
                $diff = abs($totalCoveredAutomation - $details->workingQuantity);
                $totalWorkingQuantity = $totalCoveredAutomation + $diff;
                echo "Total Covered For Automation:{$totalCoveredAutomation} || For partial: {$diff}  || total is: {$totalWorkingQuantity}";
                if ($details->workingQuantity == $totalWorkingQuantity) $this->removeExcessQuantity($lotNumber, $diff); // partial if less than
            }

            $automation->setHasExcess($flag);
            echo "<pre>" . print_r($automation, true) . "</pre><br>";

            $this->autoFinishBooking($automation);
            
        }

        // Create Notificiation here
        if (!$isExisted) 
        {
            $this->updateWorkingTable($lotNumber, 3); // 3 means no data, engineer must be notified and input data

            $subject = new Subject();
            $subject->attach(new Observer('1163'));
            $subject->attach(new Observer('0939'));
            $message = [
                'notificationDetail' => "Attention : Please input automation details for {$lotNumber} to automate nesting",
                'notificationKey'=> $lotNumber,
                'notificationLink' => "/V4/Others/Ericj/Auto%20Finish%20Booking/index.php?route=fetch/notif&",
                'notificationType' => 13 
            ];
            $subject->notify($message);
        }
    }

    private function automationDetails($lotNumber)
    {   
        $testModeData = (object)[
            'lotNumber' => '20-20-2000',
            'bookingId' => 231527, 
            'autoId' => 0, 
            'partNumber' => 'PV00977A M92',
            'materialName' => 'JFE-H400-ECOG',
            'thickNess' => '3.200',
            'width' => '1000.000',
            'height' => '1160.000',
            'quantityPerSheet' => '15',
            'nestingType' => 1,
            'sheetQuantity' => '16',
            'materialRequirement' => '17',
            'workingQuantity' => '380', 
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
                    materialRequirement,
                    autoId,
                    dataOne as materialName,
                    dataTwo as thickNess,
                    dataThree as height,
                    dataFour as width,
                    system_automation.quantityPerSheet
                FROM 
                    ppic_lotlist
                INNER JOIN 
                    cadcam_parts as parts USING (partId)
                INNER JOIN 
                    engineering_bookingdetails USING (lotNumber)
                INNER JOIN 
                    engineering_booking as book USING (bookingId) 
                LEFT JOIN 
                    system_automation ON system_automation.partNumber = parts.partNumber 
                     AND system_automation.sheetQuantity <= engineering_bookingdetails.materialRequirement
                INNER JOIN 
                    warehouse_inventory ON warehouse_inventory.inventoryId = book.inventoryId 
                WHERE lotNumber LIKE '$lotNumber' AND book.bookingStatus = 2    
                                    AND warehouse_inventory.dataTwo = system_automation.materialThickness
                					AND warehouse_inventory.dataThree = system_automation.materialHeight
                                    AND warehouse_inventory.dataFour = system_automation.materialWidth
                ORDER BY sheetQuantity DESC
            ")[0];
    }

    private function hasExistingFile($partNumber): bool
    {
   
        // Use glob to find matching files
        $pdfFiles = glob(NESTING_PATH."/{$partNumber}_*.pdf");
        $zipFiles = glob(PROGRAM_PATH."/{$partNumber}_*.zip");
        // Check if any matching files were found in both directories
        return !empty($pdfFiles) && !empty($zipFiles);
    }

    private function removeExcessQuantity($lotNumber, $diff)
    {
        partialLotNumberTest($lotNumber, $diff, 1,'system');
    }

    private function autoFinishBooking(Automation $automation)
    {
        echo "<br>Copying files paths........ <br>";
        $this->transferAutoToMainFolder([
           ACTUAL_DRAWING_PATH => [ $automation->getBookingId(), $automation->getNestingDrawing()['path'] ],
           ACTUAL_PROGRAM_PATH =>  [ $automation->getBookingId(), $automation->getNestingProgram()['path']]
        ],$automation->getHasExcess(),true);

        $lotNumber = $automation->getLotNumber();
        $processCode = $automation->getLaserNestingProcess();
        $workScheduleId = Database::fetchSql("SELECT id FROM view_workschedule WHERE lotNumber LIKE '$lotNumber' AND processCode = $processCode LIMIT 1")[0];

        if($workScheduleId) $this->finishCurrentAutomation($workScheduleId->id, $lotNumber);

    }

    private function transferAutoToMainFolder(array $paths,$type, $testMode = false)
    {
        $destination = $testMode == 1 ? TESTING_PATH : ACTUAL_PROGRAM_PATH;
        foreach($paths as $dir => $data)
        {
            $destination = $testMode ? TESTING_PATH : $dir;
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

        echo $update->getGeneratedQuery();
    }

    public function notificationUpdate($param)
    {   
        extract($param);
        if($notificationId) $this->database->table('system_notification')->setValues(['notificationStatus' => 1])->where('notificationId', $notificationId)->execute('update');
    }
}
