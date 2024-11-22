<?php 
namespace controller;

use repo\AutomationRepository;
use server\Database;
use model\Router;

class AutomationController 
{
    protected $repository;
    protected $database;
    protected $conn;
    protected $router;


    public function __construct()
    {
        $this->database = new Database;
        $this->conn = $this->database->getDB();
        $this->repository = new AutomationRepository($this->database);
        $this->router = new Router();
    }

    public function requestHandler()
    {
        $this->router->add('create/automations', [$this->repository, 'create'], 'POST');
        $this->router->add('fetch/notif', [$this->repository, 'notificationUpdate'], 'GET');
        $this->router->HttpHandlerRequest();
    }

    public function tableConfig() : array
    {
        $sql = "SELECT * FROM system_automation";
        $totalRecords = $this->conn->query($sql)->num_rows;

        return [
            $sql,
            $totalRecords
        ];
    }
}