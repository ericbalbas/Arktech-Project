<?php

namespace server\Notification;
use server\Database;
class Observer
{
    private $observerId;
    public function __construct($id)
    {
        $this->observerId = $id;
    }

    private function pushNotificationDetails(array $message)
    {   
        
    }

    public function pushNotification(array $message)
    {

    }
}


?>