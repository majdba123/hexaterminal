<?php

namespace App\Notifications;

use App\Models\ContactLead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Queued internal alert for a new legitimate lead. Deliberately carries a
 * SUMMARY only (name, email, intent, company, score) -- never the full
 * payload -- so lead details stay in the CMS rather than in mailboxes and
 * mail-provider logs. Recipients come from Company Settings (Filament) with
 * an env fallback; when neither is configured the caller skips sending
 * entirely (safe disabled mode, no fake success).
 */
class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactLead $lead) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $cmsUrl = rtrim((string) config('app.url'), '/').'/cms/contact-leads/'.$this->lead->id.'/edit';

        return (new MailMessage)
            ->subject('New lead: '.$this->lead->name.' ('.$this->lead->intent.')')
            ->line('A new lead was submitted on the website.')
            ->line('Name: '.$this->lead->name)
            ->line('Email: '.$this->lead->email)
            ->line('Intent: '.$this->lead->intent)
            ->line('Company: '.($this->lead->company ?: '—'))
            ->line('Score: '.($this->lead->score ?? '—').' ('.$this->lead->priority.' priority)')
            ->action('Open in CMS', $cmsUrl)
            ->line('Full details are available in the CMS only.');
    }
}
