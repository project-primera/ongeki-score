<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\Storage;

class SlackNotification extends Notification
{
    use Queueable;
    

    private $name;
    private $content;
    private $fields = [];
    private $type;
    private $fileContent;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $content, $fileContent, $fields, string $type, string $name)
    {
        $this->name = $name;
        $this->content = $content;
        $this->fields = $fields;
        $this->type = $type;
        $this->fileContent = $fileContent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * 通知のSlackプレゼンテーションを取得
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable) {
        $slack = (new SlackMessage)
            ->from($this->name)
            ->content($this->content);
            
        if($this->fileContent !== false){
            $fileName = time() . "_" . mt_rand();
            $filePath = "/log/exceptions/" . $fileName;
            $url = url('/admin' . $filePath);
            Storage::put($filePath, $this->fileContent);

            $slack->attachment(function ($attachment) use ($url) {
                $attachment->title($url, $url)
                ->fields($this->fields);
            });
        }

        switch(true){
            case ($this->type === "error"):     $slack->error();    break;
            case ($this->type === "warning"):   $slack->warning();  break;
            case ($this->type === "success"):   $slack->success();  break;
        }

        return $slack;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
