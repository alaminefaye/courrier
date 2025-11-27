<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourrierUrgent extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($courrier, $type = 'entrant')
    {
        $this->courrier = $courrier;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $nim = $this->courrier->nim;
        $url = $this->type === 'entrant' 
            ? route('courriers.entrants.show', $this->courrier->id)
            : route('courriers.sortants.show', $this->courrier->id);

        return (new MailMessage)
            ->subject('⚠️ COURRIER URGENT - ' . $nim)
            ->line('Un courrier URGENT nécessite votre attention immédiate !')
            ->line('NIM: ' . $nim)
            ->action('Voir le courrier', $url)
            ->line('Merci de traiter ce courrier en priorité.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'courrier_urgent',
            'courrier_id' => $this->courrier->id,
            'courrier_type' => $this->type,
            'nim' => $this->courrier->nim,
            'message' => '⚠️ COURRIER URGENT : ' . $this->courrier->nim,
            'url' => $this->type === 'entrant' 
                ? route('courriers.entrants.show', $this->courrier->id)
                : route('courriers.sortants.show', $this->courrier->id),
        ];
    }
}
