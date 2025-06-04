<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function sendEmail($user, $mailable)
    {
        // Set konfigurasi email dinamis
        Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
        Config::set('mail.mailers.smtp.port', 587);
        Config::set('mail.mailers.smtp.encryption', 'tls');
        Config::set('mail.mailers.smtp.username', $user->email_provider);
        Config::set('mail.mailers.smtp.password', $user->email_provider_password); // Simpan password dengan aman!

        // Kirim email
        Mail::to($user->email)->send($mailable);
    }
}
