<?php
namespace App\Services;

class Slack {
    public function Default(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack(""))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Default"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Debug(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("debug"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Debug"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Info(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("info"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name , "Info"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Notice(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("notice"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Notice"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Warning(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("warning"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Warning"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Error(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("error"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Error"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Critical(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("critical"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Critical"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Alert(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("alert"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Alert"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }

    public function Emergency(string $content, $fileContent = false, $fields = [], string $type = "", string $name = "LaravelLogger"){
        try {
            (new \App\NotifySlack("emergency"))->notify(new \App\Notifications\SlackNotification($content, $fileContent, $fields, $type, $name, "Emergency"));
        } catch (\Throwable $th) {
            // なにもしない
        }
    }
}
