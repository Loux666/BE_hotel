<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The refund instance.
     *
     * @var mixed
     */
    public $refund;

    /**
     * Create a new message instance.
     *
     * @param mixed $refund
     */
    public function __construct($refund)
    {
        $this->refund = $refund;
    }



    public function build()
    {
        return $this->subject('Hoàn tiền thành công đơn #' . $this->refund->booking_id)
            ->view('email.refund_success');
    }
}
