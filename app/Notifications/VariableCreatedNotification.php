<?php

namespace App\Notifications;

use App\Variable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class VariableCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Variable
     */
    public $variable;

    /**
     * Create a new notification instance.
     *
     * @param \App\Variable $variable
     * @return void
     */
    public function __construct(Variable $variable)
    {
        $this->variable = $variable;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $channel = $notifiable->slack_notification_channel ? "#{$notifiable->slack_notification_channel}" : '#general';

        return (new SlackMessage())
            ->success()
            ->from(config('app.name'))
            ->image(url('/images/icon.png'))
            ->to($channel)
            ->content('An environment variable has been added!')
            ->attachment(function ($attachment) use ($notifiable) {
                $attachment->title($notifiable->name, route('apps.show', [
                    'app' => $notifiable->id,
                ]))
                    ->content('Please `php artisan envault:sync` your environment!')
                    ->fields([
                        'Key' => $this->variable->key,
                        'Version' => "v{$this->variable->latest_version->id}",
                    ]);
            });
    }
}
