<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class NotifySlack extends Model
{
    use Notifiable;

    private $webhook;
    
    public function __construct(string $level){
        switch(true) {
            case ($level === "debug"):      $this->webhook = env('SLACK_WEBHOOK_URL_DEBUG');        break;
            case ($level === "info"):       $this->webhook = env('SLACK_WEBHOOK_URL_INFO');         break;
            case ($level === "notice"):     $this->webhook = env('SLACK_WEBHOOK_URL_NOTICE');       break;
            case ($level === "warning"):    $this->webhook = env('SLACK_WEBHOOK_URL_WARNING');      break;
            case ($level === "error"):      $this->webhook = env('SLACK_WEBHOOK_URL_ERROR');        break;
            case ($level === "critical"):   $this->webhook = env('SLACK_WEBHOOK_URL_CRITICAL');     break;
            case ($level === "alert"):      $this->webhook = env('SLACK_WEBHOOK_URL_ALERT');        break;
            case ($level === "emergency"):  $this->webhook = env('SLACK_WEBHOOK_URL_EMERGENCY');    break;
            default:                        $this->webhook = env('SLACK_WEBHOOK_URL_DEFAULT');          break;
        }
    }

    public function routeNotificationForSlack()
    {
        return $this->webhook;
    }
}
