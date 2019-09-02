<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class NotifySlack extends Model
{
    use Notifiable;

    private $webhook;
    
    public function __construct(string $level){
        if(config('app.debug')){
            $this->webhook = config('env.slack-webhook-url-default');
        }else{
            switch(true) {
                case ($level === "debug"):      $this->webhook = config('env.slack-webhook-url-debug');        break;
                case ($level === "info"):       $this->webhook = config('env.slack-webhook-url-info');         break;
                case ($level === "notice"):     $this->webhook = config('env.slack-webhook-url-notice');       break;
                case ($level === "warning"):    $this->webhook = config('env.slack-webhook-url-warning');      break;
                case ($level === "error"):      $this->webhook = config('env.slack-webhook-url-error');        break;
                case ($level === "critical"):   $this->webhook = config('env.slack-webhook-url-critical');     break;
                case ($level === "alert"):      $this->webhook = config('env.slack-webhook-url-alert');        break;
                case ($level === "emergency"):  $this->webhook = config('env.slack-webhook-url-emergency');    break;
                default:                        $this->webhook = config('env.slack-webhook-url-default');      break;
            }
        }
    }

    public function routeNotificationForSlack()
    {
        return $this->webhook;
    }
}
