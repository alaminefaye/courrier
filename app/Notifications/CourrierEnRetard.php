<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourrierEnRetard extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;

    /**
     * Create a new notification instance.
     */
    public function __construct($courrier)
    {
        $this->courrier = $courrier;
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
        $url = route('courriers.entrants.show', $this->courrier->id);
        $joursRetard = now()->diffInDays($this->courrier->date_arrivee);

        return (new MailMessage)
            ->subject('⚠️ Courrier en retard - ' . $nim)
            ->line('Un courrier entrant est en retard depuis ' . $joursRetard . ' jour(s).')
            ->line('NIM: ' . $nim)
            ->line('Date d\'arrivée: ' . $this->courrier->date_arrivee->format('d/m/Y'))
            ->action('Voir le courrier', $url)
            ->line('Merci de traiter ce courrier rapidement.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'courrier_en_retard',
            'courrier_id' => $this->courrier->id,
            'nim' => $this->courrier->nim,
            'message' => '⚠️ Courrier en retard : ' . $this->courrier->nim,
            'url' => route('courriers.entrants.show', $this->courrier->id),
        ];
    }
}
