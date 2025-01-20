<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $to, Mailable $mail)
    {
        $this->onQueue('common_send_email');
        $this->to = $to;
        $this->mail = $mail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::to($this->to)->send($this->mail);
        } catch (\Throwable $e) {
            Log::error('send_mail' . $e);
            throw new \Exception($e);
        }
    }
}
