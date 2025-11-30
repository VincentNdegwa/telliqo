<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Comment $comment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $customer = $this->comment->commentable;

        return (new MailMessage)
            ->subject('New comment on a customer')
            ->line("{$this->comment->user->name} added a comment about customer {$customer->name}.")
            ->line($this->comment->body)
            ->action('View customer', route('customers.show', $customer));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'customer_id' => $this->comment->commentable_id,
            'business_id' => $this->comment->business_id,
            'body' => $this->comment->body,
        ];
    }
}
