<?php namespace App\Http\Controllers\ClientArea;
use App\Invoicer\Repositories\Contracts\InvoiceInterface as Invoice;
use App\Invoicer\Repositories\Contracts\ProductInterface as Product;
use App\Invoicer\Repositories\Contracts\ClientInterface as Client;
use App\Invoicer\Repositories\Contracts\EstimateInterface as Estimate;
use App\Invoicer\Repositories\Contracts\PaymentInterface as Payment;
use App\Invoicer\Repositories\Contracts\ExpenseInterface as Expense;
class HomeController extends Controller {
    protected $invoice, $product, $client, $estimate, $payment, $expense;
    /**
     * Create a new controller instance.
     */
    public function __construct(Invoice $invoice, Product $product, Client $client, Estimate $estimate, Payment $payment, Expense $expense)
	{
        $this->invoice      = $invoice;
        $this->product      = $product;
        $this->client       = $client;
        $this->estimate     = $estimate;
        $this->payment      = $payment;
        $this->expense      = $expense;
	}
	/**
	 * Show the application dashboard to the user.
	 *
	 */
	public function index()
	{
	    $logged_user = auth()->guard('user')->user();
        $invoices = $logged_user->invoices->count();
        $estimates = $logged_user->estimates->count();
        $recentInvoices = $logged_user->invoices->take(10);
        $recentEstimates = $logged_user->estimates->take(10);
        $invoice_stats['unpaid']        = $logged_user->invoices->where('status', getStatus('status', 'unpaid'))->count();
        $invoice_stats['paid']          = $logged_user->invoices->where('status', getStatus('status', 'paid'))->count();
        $invoice_stats['partiallyPaid'] = $logged_user->invoices->where('status', getStatus('status', 'partially_paid'))->count();
        $invoice_stats['overdue']       = $logged_user->invoices->where('status', getStatus('status', 'overdue'))->count();
        $total_outstanding              = $this->invoice->totalClientUnpaidAmount($logged_user->uuid);
        $total_payments = 0;
        foreach ($logged_user->invoices as $invoice){
            foreach ($invoice->payments as $payment){
                $total_payments += currency_convert(getCurrencyId($invoice->currency),$payment->amount);
            }
        }
        $total_payments = defaultCurrency(true).format_amount($total_payments);
		return view('clientarea.home', compact('invoices','estimates','recentInvoices','recentEstimates', 'invoice_stats','total_payments','total_outstanding'));
	}
}
