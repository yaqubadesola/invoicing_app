<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidModel;
class Product extends Model {
    use UuidModel;
    public $incrementing = false;
    protected $primaryKey = 'uuid';
    protected $fillable =  ['name', 'code', 'category_id', 'price', 'description','image'];

    public static function boot() {
        parent::boot();
        static::deleting(function($product) {
            if($product->image != ''){
                \File::delete(config('app.images_path').'uploads/product_images/'.$product->image);
            }
        });
    }

    public function category(){
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function scopeOrdered($query){
        return $query->orderBy('created_at', 'desc')->get();
    }
}
