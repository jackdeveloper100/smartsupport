<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    protected $to;
    protected $subject;
    protected $body;
    /**
     * Create a new job instance.
     */
    public function __construct($to, $subject, $body)
    {
        //
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $to = $this->to;
        $subject = $this->subject;
        try {
            \Illuminate\Support\Facades\Mail::send('email.layouts.container', ['body' => $this->body], function ($m) use ($to, $subject) {
                $m->from(config('setting.mail_from_address'), config('setting.mail_from_name'));
                $m->to($to)->subject($subject);
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Email Failed : ' . $to . ' : ' . $subject.' Error : '.$e->getMessage());
        }
    }
}
