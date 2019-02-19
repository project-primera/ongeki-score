<?php
namespace App\Services;

class Slack {
    public function Default(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack(""))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Default"));
    }
    public function Debug(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("debug"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Debug"));
    }
    public function Info(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("info"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name , "Info"));
    }
    public function Notice(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("notice"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Notice"));
    }
    public function Warning(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("warning"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Warning"));
    }
    public function Error(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("error"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Error"));
    }
    public function Critical(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("critical"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Critical"));
    }
    public function Alert(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("alert"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Alert"));
    }
    public function Emergency(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("emergency"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Emergency"));
    }
}