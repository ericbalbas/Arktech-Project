<?php

namespace model;

/**
 * Class Automation
 *
 * Represents an automation process with various attributes like material, size, and more.
 */
class Automation
{
    /** @var string Automator name */
    public $automator;

    /** @var int Booking ID */
    public $bookingId;

    /** @var int Booking ID */
    public $lotNumber;

    /** @var int Purchase Order ID (private to the class) */
    private $purchaseOrderId;

    /** @var string Part number of the item */
    protected $partNumber;

    /** @var int sheetQuantity of the item */
    protected $sheetQuantity;

    /** @var string Cutting condition */
    protected $cuttingCondition;

    /** @var array Path(s) to the nesting drawing file(s) */
    protected $nestingDrawing; // file;

    /** @var array Path(s) to the nesting program ZIP file(s) */
    protected $nestingProgram; // zip;

    /** @var string Material type */
    protected $materialName;

    /** @var float materialThickness of the material */
    protected $materialThickness;

    /** @var string materialHeight of the material */
    protected $materialHeight;

    /** @var string materialWidth of the material */
    protected $materialWidth;

    /** @var string Machine used for the automation */
    protected $machine;

    /** @var float Process time required for the automation */
    protected $processTime;

    /** @var int quantityOfSheet required for the automation */
    protected $quantityOfSheet;

    /** @var bool hasExcess required for the automation */
    protected $hasExcess;

    protected $laserNestingProcess;

    protected $workingQuantity;

    protected $filler;

    protected $autoId;

    /**
     * Get the Purchase Order ID.
     *
     * @return int
     */
    public function getPurchaseOrderId(): int
    {
        return $this->purchaseOrderId;
    }

    /**
     * Set the Purchase Order ID.
     *
     * @param int $purchaseOrderId
     */
    public function setPurchaseOrderId(int $purchaseOrderId)
    {
        $this->purchaseOrderId = $purchaseOrderId;

        return $this;
    }

    /**
     * Get the Part Number.
     *
     * @return string
     */
    public function getPartNumber(): string
    {
        return $this->partNumber;
    }

    /**
     * Set the Part Number.
     *
     * @param string $partNumber
     */
    public function setPartNumber(string $partNumber)
    {
        $this->partNumber = $partNumber;

        return $this;
    }

    /**
     * Get the sheetQuantity.
     *
     * @return int
     */
    public function getsheetQuantity(): int
    {
        return $this->sheetQuantity;
    }

    /**
     * Set the sheetQuantity.
     *
     * @param int $sheetQuantity
     */
    public function setsheetQuantity(int $sheetQuantity)
    {
        $this->sheetQuantity = $sheetQuantity;

        return $this;
    }

    /**
     * Get the Cutting Condition.
     *
     * @return string
     */
    public function getCuttingCondition(): string
    {
        return $this->cuttingCondition;
    }

    /**
     * Set the Cutting Condition.
     *
     * @param string $cuttingCondition
     */
    public function setCuttingCondition(string $cuttingCondition)
    {
        $this->cuttingCondition = $cuttingCondition;

        return $this;
    }

    /**
     * Get the Nesting Drawing file paths.
     *
     * @return 
     */
    public function getNestingDrawing() : array
    {
        return $this->nestingDrawing;
    }

    /**
     * Set the Nesting Drawing file paths.
     *
     * @param array $nestingDrawing
     */
    public function setNestingDrawing(array $nestingDrawing)
    {
        $this->nestingDrawing = $nestingDrawing;

        return $this;
    }

    /**
     * Get the Nesting Program ZIP file paths.
     *
     * @return array
     */
    public function getNestingProgram() : array
    {
        return $this->nestingProgram;
    }

    /**
     * Set the Nesting Program ZIP file paths.
     *
     * @param array $nestingProgram
     */
    public function setNestingProgram(array $nestingProgram)
    {
        $this->nestingProgram = $nestingProgram;
        return $this;

    }

    /**
     * Get the materialThickness.
     *
     * @return float
     */
    public function getmaterialThickness(): string
    {
        return $this->materialThickness;
    }

    /**
     * Set the materialThickness.
     *
     * @param float $materialThickness
     */
    public function setmaterialThickness($materialThickness)
    {
        $this->materialThickness = $materialThickness;

        return $this;
    }
  
    /**
     * Get the Machine.
     *
     * @return string
     */
    public function getMachine(): string
    {
        return $this->machine;
    }

    /**
     * Set the Machine.
     *
     * @param string $machine
     */
    public function setMachine(string $machine)
    {
        $this->machine = $machine;

        return $this;
    }

    /**
     * Get the Process Time.
     *
     * @return float
     */
    public function getProcessTime(): float
    {
        return $this->processTime;
    }

    /**
     * Set the Process Time.
     *
     * @param float $processTime
     */
    public function setProcessTime(float $processTime)
    {
        $this->processTime = $processTime;

        return $this;
    }

    /**
     * Get the value of materialWidth
     */ 
    public function getmaterialWidth()
    {
        return $this->materialWidth;
    }

    /**
     * Set the value of materialWidth
     *
     * @return  self
     */ 
    public function setmaterialWidth($materialWidth)
    {
        $this->materialWidth = $materialWidth;
        return $this;

    }

    /**
     * Get the value of materialHeight
     */ 
    public function getmaterialHeight()
    {
        return $this->materialHeight;
    }

    /**
     * Set the value of materialHeight
     *
     * @return  self
     */ 
    public function setMaterialHeight($materialHeight)
    {
        $this->materialHeight = $materialHeight;
        return $this;
    }

    /**
     * Get the value of lotNumber
     */ 
    public function getLotNumber()
    {
        return $this->lotNumber;
    }

    /**
     * Set the value of lotNumber
     *
     * @return  self
     */ 
    public function setLotNumber($lotNumber)
    {
        $this->lotNumber = $lotNumber;

        return $this;
    }

    /**
     * Get the value of materialName
     */ 
    public function getMaterialName()
    {
        return $this->materialName;
    }

    /**
     * Set the value of materialName
     *
     * @return  self
     */ 
    public function setMaterialName($materialName)
    {
        $this->materialName = $materialName;

        return $this;
    }

    /**
     * Get the value of quantityOfSheet
     */ 
    public function getQuantityOfSheet()
    {
        return $this->quantityOfSheet;
    }

    /**
     * Set the value of quantityOfSheet
     *
     * @return  self
     */ 
    public function setQuantityOfSheet($quantityOfSheet)
    {
        $this->quantityOfSheet = $quantityOfSheet;

        return $this;
    }

    /**
     * Get the value of bookingId
     */ 
    public function getBookingId()
    {
        return $this->bookingId;
    }

    /**
     * Set the value of bookingId
     *
     * @return  self
     */ 
    public function setBookingId($bookingId)
    {
        $this->bookingId = $bookingId;

        return $this;
    }

    /**
     * Get the value of hasExcess
     */ 
    public function getHasExcess() : bool
    {
        return $this->hasExcess;
    }

    /**
     * Set the value of hasExcess
     *
     * @return  self
     */ 
    public function setHasExcess($hasExcess)
    {
        $this->hasExcess = $hasExcess;

        return $this;
    }

    /**
     * Get the value of laserNestingProcess
     */ 
    public function getLaserNestingProcess() : int
    {
        return $this->laserNestingProcess;
    }

    /**
     * Set the value of laserNestingProcess
     *
     * @return  self
     */ 
    public function setLaserNestingProcess($laserNestingProcess) : self
    {
        $this->laserNestingProcess = $laserNestingProcess;

        return $this;
    }

    /**
     * Get the value of workingQuantity
     */ 
    public function getWorkingQuantity()
    {
        return $this->workingQuantity;
    }

    /**
     * Set the value of workingQuantity
     *
     * @return  self
     */ 
    public function setWorkingQuantity($workingQuantity)
    {
        $this->workingQuantity = $workingQuantity;

        return $this;
    }

    /**
     * Get the value of filler
     */ 
    public function getFiller()
    {
        return $this->filler;
    }

    /**
     * Set the value of filler
     *
     * @return  self
     */ 
    public function setFiller($filler)
    {
        $this->filler = $filler;

        return $this;
    }

    /**
     * Get the value of autoId
     */ 
    public function getAutoId()
    {
        return $this->autoId;
    }

    /**
     * Set the value of autoId
     *
     * @return  self
     */ 
    public function setAutoId($autoId)
    {
        $this->autoId = $autoId;

        return $this;
    }
}
