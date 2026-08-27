<?php

namespace App\Jobs;

use App\Mail\DocumentNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Email;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use App\Helpers\General;

class SendDocumentNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $description;
    public $DescriptionType;
    public $documentType;
    public $template;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $description, $DescriptionType, $documentType,$template)
    {
        $this->user = $user;
        $this->description = $description;
        $this->DescriptionType = $DescriptionType;
        $this->documentType = $documentType;
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new General())->sendEmail($user->email,  $template, [
                'name' => $user->first_name . ' ' . $user->last_name,
                'description' => $description,
                'DescriptionType' => $DescriptionType,
                'document_name' => $documentType
            ]);
        // $emailJob = new DocumentNotificationMail($this->user, $this->description, $this->DescriptionType, $this->documentType);
        // Mail::to($this->user->email)->send($emailJob); // Send the email
    }
}
