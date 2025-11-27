<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouveauCourrier extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courrier;
    protected $type; // 'entrant' ou 'sortant'

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
            ->subject('Nouveau courrier reçu - ' . $nim)
            ->line('Un nouveau courrier ' . ($this->type === 'entrant' ? 'entrant' : 'sortant') . ' a été enregistré.')
            ->line('NIM: ' . $nim)
            ->action('Voir le courrier', $url)
            ->line('Merci d\'utiliser notre système de gestion de courrier !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'nouveau_courrier',
            'courrier_id' => $this->courrier->id,
            'courrier_type' => $this->type,
            'nim' => $this->courrier->nim,
            'message' => 'Nouveau courrier ' . ($this->type === 'entrant' ? 'entrant' : 'sortant') . ' : ' . $this->courrier->nim,
            'url' => $this->type === 'entrant' 
                ? route('courriers.entrants.show', $this->courrier->id)
                : route('courriers.sortants.show', $this->courrier->id),
        ];
    }
}
