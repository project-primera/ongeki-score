<?php
namespace App\Services;

class Slack {
    public function Debug(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("debug"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Info(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("info"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Notice(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("notice"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Warning(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("warning"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Error(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("error"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Critical(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("critical"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Alert(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("alert"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
    public function Emergency(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        (new \App\NotifySlack("emergency"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name));
    }
}