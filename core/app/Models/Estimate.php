<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidModel;
use App\Invoicer\Repositories\Eloquent\EstimateRepository as EstimateRepo;
use Illuminate\Support\Facades\App;

class Estimate extends Model {
    use UuidModel;
    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $appends = ['totals'];
    protected  $fillable = ['client_id','estimate_no','estimate_title','estimate_date','currency','notes','terms'];

    public function client(){
        return $this->belongsTo(Client::class, 'client_id');
    }
    public function items(){
        return $this->hasMany(EstimateItem::class,'estimate_id');
    }
    public function scopeOrdered($query){
        return $query->orderBy('created_at', 'desc')->get();
    }
    function getTotalsAttribute() {
        $repo = App::make(EstimateRepo::class);
        $totals = $repo->estimateTotals($this->uuid);
        return $totals;
    }
}