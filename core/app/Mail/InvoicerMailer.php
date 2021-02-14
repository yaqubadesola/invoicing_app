<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoicerMailer extends Mailable
{
    use Queueable, SerializesModels;
    public $params;
    public $subject;
    public $from_email;
    public $from_name;
    public $template;
    public $template_type;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params        = $params;
        $this->data          = isset($this->params['data']) ? $this->params['data'] : '';
        $this->subject       = isset($this->params['subject']) ? $this->params['subject'] : '';
        $this->from_email    = config('mail.from.address');
        $this->from_name     = config('mail.from.name');
        $this->template     =  $this->params['template'];
        $this->template_type = isset($this->params['template_type']) ? $this->params['template_type'] : 'view';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->template_type == 'view') {
            $message = $this->view($this->template)
                ->from($this->from_email, $this->from_name)
                ->with('data', $this->data);
            if(isset($this->data['attachment'])){
                $message->attach($this->data['attachment']);
            }
            return $message;
        }
        else {
            $message = $this->markdown($this->template)
                ->from($this->from_email, $this->from_name)
                ->with('data', $this->data)
                ->subject($this->subject);
            if(isset($this->data['attachment'])){
                $message->attach($this->data['attachment']);
            }
            return $message;
        }
    }
}
