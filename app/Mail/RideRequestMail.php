<?php

namespace App\Mail;

use App\Models\RideRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RideRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly RideRequest $rideRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Ride Request — Chariot',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ride_request',
        );
    }
}
