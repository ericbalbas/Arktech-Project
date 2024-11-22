<?php

namespace server\Notification;
use server\Notification\Observer;
class Subject {
    private $observers = [];


    public function attach(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    // Notify all observers
    public function notify($message)
    {
        // $this->notificationLog[] = $message;
        foreach ($this->observers as $observer) {
            $observer->notify($message);
        }
    }
}


?>