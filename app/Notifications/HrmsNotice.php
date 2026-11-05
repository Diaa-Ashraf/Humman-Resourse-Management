<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HrmsNotice extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $url,
        public string $category = 'تنبيه'
    ) {}

    public function via(object $notifiable): array
    {
        return config('hrms.notify_mail') ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'url' => $this->url, 'category' => $this->category];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('نظام الموارد البشرية: '.$this->title)->greeting('مرحبًا '.$notifiable->name)->line($this->message)->action('فتح النظام', url($this->url));
    }
}
