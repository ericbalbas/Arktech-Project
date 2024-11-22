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

    private function postNotification(array $message)
    {   
        // insert data to notificationdetails
        $insert = new Database;

        $insert->table('system_notificationdetails')
            ->setValues($message)
            ->execute("insert");

        $_lastId = $insert->lastInsertId();

        $notifData = [
            'notificationId' => $_lastId,
            'notificationTarget' => $this->observerId,
            'targetType' => 2,
        ];

        $insert->table('system_notification')
            ->setValues($notifData)
            ->execute("insert");
        
    }

    public function notify(array $message)
    {
       $this->postNotification($message);
    }
}


?>