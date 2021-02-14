<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidModel;
use Illuminate\Support\Facades\App;
use App\Invoicer\Repositories\Eloquent\InvoiceRepository as InvoiceRepo;
class Invoice extends Model {
    use UuidModel;
    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $fillable = ['client_id', 'number', 'invoice_date', 'due_date', 'status', 'discount', 'terms', 'notes', 'currency','discount_mode','recurring','recurring_cycle'];
    protected $appends = ['totals'];
    public function client(){
        return $this->belongsTo(Client::class, 'client_id');
    }
    public function items(){
        return $this->hasMany(InvoiceItem::class,'invoice_id');
    }
    public function payments(){
        return $this->hasMany(Payment::class,'invoice_id');
    }
    public function scopeOrdered($query){
        return $query->orderBy('created_at', 'desc')->get();
    }
    function getTotalsAttribute(){
        $repo = App::make(InvoiceRepo::class);
        $totals = $repo->invoiceTotals($this->uuid);
        return $totals;
    }
}
