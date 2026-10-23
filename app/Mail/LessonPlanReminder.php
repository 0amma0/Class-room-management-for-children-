<?php

namespace App\Mail;

use App\Models\LessonPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonPlanReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LessonPlan $lessonPlan) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat kegiatan: '.$this->lessonPlan->theme,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.lesson-plan-reminder',
            with: [
                'lessonPlan' => $this->lessonPlan,
                'calendarUrl' => $this->lessonPlan->googleCalendarUrl(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
