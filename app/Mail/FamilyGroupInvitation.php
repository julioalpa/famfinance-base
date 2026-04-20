<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyGroupInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Te invitaron a unirte al grupo \"{$this->invitation->familyGroup->name}\" en FamFinance",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'groupName'    => $this->invitation->familyGroup->name,
                'invitedBy'    => $this->invitation->invitedBy->name,
                'acceptUrl'    => route('invitations.accept', $this->invitation->token),
                'expiresAt'    => $this->invitation->expires_at->format('d/m/Y'),
            ],
        );
    }
}
