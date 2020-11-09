<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Intervention\Image\Facades\Image;
use App\Models\Section;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function products(){
        Session::put('page', 'products');
        $products = Product::with([
            'category' => function($query){
                $query->select('id', 'category_name');
            },
            'section' => function($query){
                $query->select('id', 'name');
            }
        ])->get();
       /*  $products = json_decode(json_encode($products));
        echo "<pre>"; print_r($products); die; */
        return view('admin.pages.products.index')->with(compact('products'));
    }

    // Update Product Status
    public function updateProductStatus(Request $request){
        if($request->ajax()){
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;
            if($data['status']=='Active'){
                $status = 0;
            }else{
                $status = 1;
            }
            Product::where('id', $data['product_id'])->update(['status'=>$status]);
            return response()->json(['status' => $status, 'product_id'=>$data['product_id']]);
        }
    }

    // Add Product

    public function addEditProduct(Request $request, $id=null){
        if($id == ""){
            $title = "Add Product";

            $product = new Product;


        }else{
            $title = "Edit Product";
        }

        if($request->isMethod('post')){
            $data = $request->all();
            /* echo "<pre>"; print_r($data); die; */

            // Product Validation
            $rules = [
                'category_id' => 'required',
                'product_name' => 'required|regex:/^[\pL\s\-]+$/u',
                'product_code' => 'required|regex:/^[\w-]*$/',
                'product_price'=> 'required|numeric',
               
            ];

            $customMessage = [
                'category_id.required' => 'Category is required',
                'product_name.required' => 'Product Name is required',
                'product_name.regex' => 'Valid Product Name is required',
                'product_code.required' => 'Product Code is required',
                'product_code.regex' => 'Valid Product Code is required',
                'product_price.required'=> 'Product Price is required',
                'product_price.numeric' => 'Valid Product price is required'
               
            ];
            $this->validate($request, $rules, $customMessage);
            
            if(empty($data['is_featured'])){
                $is_featured = "No";
            }else{
                $is_featured = "Yes";
            }

            if(empty($data['product_discount'])){
                $data['product_discount'] = 0;
            }
            if(empty($data['product_weight'])){
                $data['product_weight'] = 0;
            }
            if(empty($data['description'])){
                $data['description'] = "";
            }
            if(empty($data['wash_care'])){
                $data['wash_care']= "";
            }
            if(empty($data['fabric'])){
                $data['fabric']= "";
            }
            if(empty($data['pattern'])){
                $data['pattern']= "";
            }
            if(empty($data['fit'])){
                $data['fit']= "";
            }
            if(empty($data['sleeve'])){
                $data['sleeve']= "";
            }
            if(empty($data['occasion'])){
                $data['occasion']= "";
            }
            if(empty($data['meta_title'])){
                $data['meta_title']= "";
            }
            if(empty($data['meta_keywords'])){
                $data['meta_keywords']= "";
            }
            if(empty($data['meta_description'])){
                $data['meta_description']= "";
            }
            if(empty($data['main_image'])){
                $data['main_image'] = "";
            }
            if(empty($data['product_video'])){
                $data['product_video'] = "";
            }

            // Upload product image
            if($request->hasFile('main_image')){
                $image_tmp = $request->file('main_image');
                if($image_tmp->isValid()){
                    // Upload Images after Resize
                    $image_name = $image_tmp->getClientOriginalName();
                    $image_name = pathinfo($image_name,PATHINFO_FILENAME);
                    $extension = $image_tmp->getClientOriginalExtension();
                   /*  echo $image_name; echo"<br>"; echo $extension; die; */
                    $imageName = $image_name.'-'.rand(111,9999).'.'.$extension;
                    /* dd($imageName); */
                    $large_image_path = 'images/product_images/large/'.$imageName;
                    $medium_image_path = 'images/product_images/medium/'.$imageName;
                    $small_image_path = 'images/product_images/small/'.$imageName;

                    Image::make($image_tmp)->save($large_image_path); // W: 1040 H: 1200
                    Image::make($image_tmp)->resize(520, 600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(260, 300)->save($small_image_path);
                    // Save main image to products table
                    $product->main_image = $imageName;
                }
            }
            // Upload Product Video
             // Upload product image
             if($request->hasFile('product_video')){
                $video_tmp = $request->file('product_video');
                if($video_tmp->isValid()){
                    // Upload Video
                    $video_name = $video_tmp->getClientOriginalName();
                    $video_name = pathinfo($video_name,PATHINFO_FILENAME);
                    $extension = $video_tmp->getClientOriginalExtension();
                    $videoName = $video_name.'-'.rand().'.'.$extension;
                    $video_path = 'videos/product_videos/'.$imageName;
                    $video_tmp->move($video_path, $videoName);
                    // Save Video to products table
                    $product->product_video = $videoName;
                }
            }

            // Save product details in products table 
            $categoryDetails = Category::find($data['category_id']);
            /*echo"<pre>"; print_r($categoryDetails); die;*/
            $product->section_id = $categoryDetails['section_id'];
            $product->category_id = $data['category_id'];
            $product->product_name = $data['product_name'];
            $product->product_code = $data['product_code'];
            $product->product_color = $data['product_color'];
            $product->product_price = $data['product_price'];
            $product->product_discount = $data['product_discount'];
            $product->product_weight = $data['product_weight'];
           /*  $product->main_image = $data['main_image']; */
           /*  $product->product_video = $data['product_video']; */
            $product->description = $data['description'];
            $product->wash_care = $data['wash_care'];
            $product->fabric = $data['fabric'];
            $product->pattern = $data['pattern'];
            $product->fit = $data['fit'];
            $product->sleeve = $data['sleeve'];
            $product->occasion = $data['occasion'];
            $product->meta_title = $data['meta_title'];
            $product->meta_keywords = $data['meta_keywords'];
            $product->meta_description = $data['meta_description'];
            $product->is_featured = $is_featured;
            $product->status = 1;
            $product->save();
            session::flash('success_message', 'Product added successfully');
            return redirect('admin/products');
           
        }

       
        
        // Filter Arrays
        $fabricArray = array('Cotton', 'Polyester', 'Wool');
        $sleeveArray = array('Full Sleeve', 'Half Sleeve', 'Short Sleeve', 'Sleeveless');
        $patternArray = array('Checked', 'Plain', 'Printed', 'Self', 'Solid');
        $fitArray = array('Regular', 'Slim');
        $occasionArray = array('Casual', 'Formal');

        // Section with Categories and Sub Categories

        $categories = Section::with('categories')->get();

        /* $categories = json_decode(json_encode($categories), true);
        echo "<pre>"; print_r($categories); die; */
        return view('admin.pages.products.add_edit_product')->with(compact('title', 'fabricArray', 'sleeveArray', 'patternArray', 'fitArray', 'occasionArray', 'categories',));
    }

    // Delete product Status

    public function deleteProduct($id){
        // delete product
        Product::where('id', $id)->delete();
        $message = 'Product has been deleted successfully!';
        session::flash('success_message', $message);
        return redirect()->back();
    }
}
