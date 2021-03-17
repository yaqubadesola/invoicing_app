<?php

namespace App\Jobs;

use App\Mail\InvoicerMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
class InvoicerMailerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $params;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        try {

             //dd($this->params);
             if(isset($this->params['cc']) && !isset($this->params['bcc'])){
                Mail::to($this->params['to'])
                ->cc($this->params['cc'])
                ->send(new InvoicerMailer($this->params));
            }else if(!isset($this->params['cc']) && isset($this->params['bcc'])){
                Mail::to($this->params['to'])
                ->cc($this->params['bcc'])
                ->send(new InvoicerMailer($this->params));
            }else if(isset($this->params['cc']) && isset($this->params['bcc'])){
                Mail::to($this->params['to'])
                ->cc($this->params['cc'])
                ->cc($this->params['bcc'])
                ->send(new InvoicerMailer($this->params));
            }else{
                Mail::to($this->params['to'])
                ->send(new InvoicerMailer($this->params));
            }


        }

        catch (\Exception $e) {
            throw $e;
        }
    }
}
