<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidModel;
use App\Invoicer\Repositories\Eloquent\TaxSettingRepository as TaxRepo;
use Illuminate\Support\Facades\App;

class EstimateItem extends Model {
    use UuidModel;
    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected  $fillable = ['estimate_id','item_name','item_description','quantity', 'price','tax_id','item_order'];
    protected $appends = ['itemTotal','itemTaxTotal'];
    public function tax(){
        return $this->belongsTo(TaxSetting::class, 'tax_id');
    }
    public function Estimate(){
        return $this->belongsTo(EstimateItem::class, 'estimate_id');
    }
    public function getItemTotalAttribute(){
        return $this->price * $this->quantity;
    }
    public function getItemTaxTotalAttribute(){
        $tax_repo = App::make(TaxRepo::class);
        $tax = $tax_repo->getById($this->tax_id);
        return $tax ? $this->itemTotal * $tax->value/100 : 0;
    }
}