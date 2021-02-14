<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
Use App\Models\Subscription;
Use App\Models\Invoice;
use App\Invoicer\Repositories\Contracts\NumberSettingInterface as Number;
use App\Invoicer\Repositories\Contracts\InvoiceInterface as InvoiceInterface;
use App\Invoicer\Repositories\Contracts\InvoiceSettingInterface as InvoiceSetting;
use App\Invoicer\Repositories\Contracts\InvoiceItemInterface as InvoiceItem;
use App\Invoicer\Repositories\Contracts\SettingInterface as Setting;
use App\Invoicer\Repositories\Contracts\TemplateInterface as Template;
use App\Invoicer\Repositories\Contracts\EmailSettingInterface as MailSetting;
use Illuminate\Support\Facades\Mail;
use PDF;
use Schema;
class SendRecurringInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoicer:recurring-invoices';
    protected $invoice,$number,$invoiceSetting,$items,$setting,$template,$mail_setting;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send recurring invoices when their due date is today';
    private $subscriptions = null;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InvoiceInterface $invoice, Number $number,InvoiceSetting $invoiceSetting,InvoiceItem $items, Setting $setting, Template $template, MailSetting $mail_setting)
    {
        parent::__construct();
        $this->number    = $number;
        $this->invoice   = $invoice;
        $this->invoiceSetting = $invoiceSetting;
        $this->items = $items;
        $this->setting   = $setting;
        $this->template  = $template;
        $this->mail_setting = $mail_setting;
        $today = date('Y-m-d');
        if (Schema::hasTable('subscriptions')) {
            $this->subscriptions = Subscription::where('status', 1)->where('nextduedate',$today)->get();
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $settings     = $this->invoiceSetting->first();
        $start        = $settings ? $settings->start_number : 0;
        $due_date     = date('Y-m-d',strtotime("+".$settings->due_days." days"));
        $today = date('Y-m-d');
        $this->subscriptions->each(function($subscription) use ($start,$due_date,$today) {
            $parent_invoice = Invoice::where('uuid',$subscription->invoice_id)->first();
            $invoice_num  = $this->number->prefix('invoice_number', $this->invoice->generateInvoiceNum($start));
            $invoiceData = array(
                'client_id'     => $parent_invoice->client_id,
                'number'        => $invoice_num,
                'invoice_date'  => date('Y-m-d'),
                'due_date'      => $due_date,
                'notes'         => $parent_invoice->notes,
                'terms'         => $parent_invoice->terms,
                'currency'      => $parent_invoice->currency,
                'status'        => $parent_invoice->status,
                'discount'      => $parent_invoice->discount,
                'discount_mode' => $parent_invoice->discount_mode,
                'recurring'     => 0,
                'recurring_cycle' => 0
            );
            $invoice = $this->invoice->create($invoiceData);
            if($invoice){
                foreach($parent_invoice->items as $item){
                    $itemsData = array(
                        'invoice_id'        => $invoice->uuid,
                        'item_name'         => $item->item_name,
                        'item_description'  => $item->item_description,
                        'quantity'          => $item->quantity,
                        'price'             => $item->price,
                        'tax_id'            => $item->tax != '' ? $item->tax : null ,
                    );
                    $this->items->create($itemsData);
                }
                $settings     = $this->invoiceSetting->first();
                if($settings){
                    $start = $settings->start_number+1;
                    $this->invoiceSetting->updateById($settings->uuid, array('start_number'=>$start));
                }
                switch ($subscription->billingcycle) {
                    case 1:
                        $next_due_date = date("Y-m-d", strtotime("+1 month", strtotime($today)));
                        break;
                    case 2:
                        $next_due_date = date("Y-m-d", strtotime("+3 month", strtotime($today)));
                        break;
                    case 3:
                        $next_due_date = date("Y-m-d", strtotime("+6 month", strtotime($today)));
                        break;
                    case 4:
                        $next_due_date = date("Y-m-d", strtotime("+12 month", strtotime($today)));
                        break;
                    default:
                        $next_due_date = date("Y-m-d", strtotime("+12 month", strtotime($today)));
                }
                $subscription->nextduedate = $next_due_date;
                $subscription->save();
                //send an email with the invoice attached
                $this->send($invoice->uuid);
            }
        });
    }
    function send($uuid){
        $invoice = $this->invoice->getById($uuid);
        $settings = $this->setting->first();
        $mail_setting = $this->mail_setting->first();
        $invoiceSettings = $this->invoiceSetting->first();
        $invoice->pdf_logo = $invoiceSettings && $invoiceSettings->logo ? base64_img(config('app.images_path').$invoiceSettings->logo) : '';
        $data_object = new \stdClass();
        $data_object->invoice = $invoice;
        $data_object->settings = $settings;
        $data_object->client = $invoice->client;
        $data_object->user = $invoice->client;
        $pdf = PDF::loadView('invoices.pdf', compact('settings', 'invoice', 'invoiceSettings'));
        $template = $this->template->where('name', 'invoice')->first();
        $data['emailBody'] = trans('application.invoice_generated');
        $data['emailTitle'] = config('app.name');
        $subject = trans('application.invoice_generated');
        if ($template) {
           $data['emailBody'] = parse_template($data_object, $template->body);
            $subject = parse_template($data_object, $template->subject);
       }
        try {
            Mail::send('emails.layout', $data, function ($message) use($pdf,$invoice,$mail_setting,$subject) {
                $message->from($mail_setting->from_email, $mail_setting->from_name);
                $message->to($invoice->client->email, $invoice->client->name);
                $message->subject($subject);
                $message->attachData($pdf->output(), 'invoice_' . $invoice->number . '_' . date('Y-m-d') . '.pdf');
            });
            $this->info('The emails are sent successfully!');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
