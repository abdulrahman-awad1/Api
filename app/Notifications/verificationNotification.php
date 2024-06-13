<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
//use Ichtrojan\Otp\Models\Otp;
use Ichtrojan\Otp\Otp;

class verificationNotification extends Notification
{
    use Queueable;
    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    private $otb;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->message = 'use the below code for verification process';
        $this->subject = 'verification needed';
        $this->fromEmail = 'aulrahman034@gmail.com';
        $this->mailer = 'smtp';
        $this->otb =new Otp();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $otpLength = '5digits'; // Convert '5digits' to integer
        $otp = $this->otb->generate($notifiable->email, 'numeric', 6);
        return (new MailMessage)

            ->mailer('smtp')
            ->subject($this->subject)
            ->greeting('hello'.$notifiable->name)
            ->line($this->message)
            ->line('code:'. $otp->token);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
