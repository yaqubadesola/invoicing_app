<?php namespace App\Http\Controllers;
use App\Http\Requests\ProductFormRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use App\Invoicer\Repositories\Contracts\ProductInterface as Product;
use App\Invoicer\Repositories\Contracts\ProductCategoryInterface as Category;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Laracasts\Flash\Flash;
use Yajra\DataTables\DataTables;

class ProductsController extends Controller {
    private $product,$category;
    public function __construct(Product $product,Category $category){
        $this->product = $product;
        $this->category = $category;
    }
	public function index()
	{
        if (Request::ajax()){
            $model = $this->product->model();
            $payments = $model::select('uuid','name','category_id','code','image','price')->ordered();
            return DataTables::of($payments)
                ->editColumn('category', function($data){ return $data->category ? $data->category->name : ''; })
                ->editColumn('amount', function($data){ return format_amount($data->amount); })
                ->editColumn('image',function($data){
                    if($data->image != ''){
                        return  '<a href="#" data-toggle="popover" data-trigger="hover" title="'.$data->name.'" data-html="true" data-content="'.htmlentities(\Html::image(image_url('uploads/product_images/'.$data->image),'image')) .'">'.\Html::image(image_url('uploads/product_images/'.$data->image), 'image', ['style'=>'width:50px']).'</a>';
                    }else{
                        return \Html::image(image_url('uploads/product_images/no-product-image.png'), 'image', ['style'=>'width:50px']);
                    }
                })
                ->addColumn('action', '
                      @if(hasPermission(\'edit_product\')){!! edit_btn(\'products.edit\', $uuid) !!}@endif
                      @if(hasPermission(\'delete_product\')){!! delete_btn(\'products.destroy\', $uuid) !!}@endif
                ')
                ->rawColumns(['image','action'])
                ->make(true);
        }else {
            return view('products.index');
        }
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        if(!hasPermission('add_product', true)) return redirect('products');
        $categories = $this->category->categorySelect();
		return view('products.create',compact('categories'));
	}
    /**
     * Store a newly created resource in storage.
     * @param ProductFormRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(ProductFormRequest $request)
	{
        $data = array(
                    'code'      => Request::get('code'),
                    'name'      => Request::get('name'),
                    'category_id'  => Request::get('category_id'),
                    'description'=> Request::get('description'),
                    'price'      => Request::get('price'),
        );
        if ($request->hasFile('product_image')){
            $file = $request->file('product_image');
            $filename = strtolower(Str::random(50) . '.' . $file->getClientOriginalExtension());
            $file->move(config('app.images_path').'uploads/product_images', $filename);
            $canvas = Image::canvas(245, 245);
            $image = Image::make(sprintf(config('app.images_path').'uploads/product_images/%s', $filename))->resize(245, 245,
                function($constraint) {
                    $constraint->aspectRatio();
                });
            $canvas->insert($image, 'center');
            $canvas->save(sprintf(config('app.images_path').'uploads/product_images/%s', $filename));
            $data['image']= $filename;
        }

		if($this->product->create($data)){
            Flash::success(trans('application.record_created'));
            return Response::json(array('success'=>true, 'msg' => trans('application.record_created')), 201);
        }
        return Response::json(array('success'=>false, 'msg' => trans('application.record_creation_failed')), 422);
	}
	/**
	 * Show the form for editing the specified resource.
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        if(!hasPermission('edit_product', true)) return redirect('products');
        $product = $this->product->getById($id);
        $categories = $this->category->categorySelect();
		return view('products.edit', compact('product','categories'));
	}
    /**
     * Update the specified resource in storage.
     * @param ProductFormRequest $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(ProductFormRequest $request, $id)
	{
        $product = $this->product->getById($id);
        $data = array(
            'code'      => Request::get('code'),
            'name'      => Request::get('name'),
            'category_id'  => Request::get('category_id'),
            'description'=> Request::get('description'),
            'price'      => Request::get('price'),
        );
        if ($request->hasFile('product_image')){
            $file = $request->file('product_image');
            $filename = strtolower(Str::random(50) . '.' . $file->getClientOriginalExtension());
            $file->move(config('app.images_path').'uploads/product_images', $filename);
            $canvas = Image::canvas(245, 245);
            $image = Image::make(sprintf(config('app.images_path').'uploads/product_images/%s', $filename))->resize(245, 245,
                function($constraint) {
                    $constraint->aspectRatio();
                });
            $canvas->insert($image, 'center');
            $canvas->save(sprintf(config('app.images_path').'uploads/product_images/%s', $filename));
            File::delete(config('app.images_path').'uploads/product_images/'.$product->image);
            $data['image']= $filename;
        }
		if($this->product->updateById($id,$data)){
            \File::delete(config('app.images_path').'uploads/product_images/'.$product->image);
            Flash::success(trans('application.record_updated'));
            return Response::json(array('success'=>true, 'msg' => trans('application.record_updated')), 201);
        }
        return Response::json(array('success'=>false, 'msg' =>  trans('application.record_update_failed')), 422);
	}
	/**
	 * Remove the specified resource from storage.
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        if(!hasPermission('delete_product', true)) return redirect('products');
		if($this->product->deleteById($id))
            Flash::success(trans('application.record_deleted'));
        else
            Flash::error(trans('application.record_deletion_failed'));

        return redirect('products');
	}
    /**
     * @return \Illuminate\View\View
     */
    public function products_modal(){
        $products = $this->product->all();
        return view('products.products_modal', compact('products'));
    }
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function process_products_selections(){
        $selected = \Request::get('products_lookup_ids');
        $products = $this->product->whereIn('uuid', $selected)->get();
        return Response::json(array('success'=>true, 'products' => $products), 200);
    }
}
