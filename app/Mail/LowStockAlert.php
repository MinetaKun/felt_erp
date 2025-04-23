<?php

namespace App\Mail;

use App\Models\RawMaterial;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $material;

    /**
     * Create a new message instance.
     */
    public function __construct(RawMaterial $material)
    {
        $this->material = $material;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Low Stock Alert - ' . $this->material->name)
            ->markdown('emails.low-stock-alert');
    }
}
