<?php
namespace App\Services;

class Slack {
    public function Debug(string $message){
        (new \App\NotifySlack("debug"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Info(string $message){
        (new \App\NotifySlack("info"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Notice(string $message){
        (new \App\NotifySlack("notice"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Warning(string $message){
        (new \App\NotifySlack("warning"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Error(string $message){
        (new \App\NotifySlack("error"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Critical(string $message){
        (new \App\NotifySlack("critical"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Alert(string $message){
        (new \App\NotifySlack("alert"))->notify(new \App\Notifications\SlackNotification($message));
    }
    public function Emergency(string $message){
        (new \App\NotifySlack("emergency"))->notify(new \App\Notifications\SlackNotification($message));
    }
}