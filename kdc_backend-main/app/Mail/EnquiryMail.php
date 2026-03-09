<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class EnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $enquiry; // Add this property to pass data to the view
    public $patient;
    /**
     * Create a new message instance.
     *
     * @param  array  $enquiry
     */
    // public function __construct(array $enquiry)
    // {
    //     $this->enquiry = $enquiry;
    // }
    // public function __construct($enquiry)
    // {
    //     // If $enquiry is an instance of stdClass, convert it to an array
    //     if ($enquiry instanceof \stdClass) {
    //         $enquiry = (array) $enquiry;
    //     }

    //     $this->enquiry = $enquiry;
    // }
    public function __construct($enquiry, $patient)
    {
        if ($enquiry instanceof \stdClass) {
            $enquiry = (array) $enquiry;
        }

        if ($patient instanceof \stdClass) {
            $patient = (array) $patient;
        }

        $this->enquiry = $enquiry;
        $this->patient = $patient;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // subject: 'Enquiry Mail',
            from: new Address(env('MAIL_FROM_ADDRESS'), $this->patient['first_name'] . ' ' . $this->patient['surname']),
            subject: 'New Enquiry from ' . $this->patient['first_name'],
            replyTo: [
                new Address(
                    $this->patient['email'],
                    $this->patient['first_name'] . ' ' . $this->patient['surname']
                )
            ]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'enquiry_mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
