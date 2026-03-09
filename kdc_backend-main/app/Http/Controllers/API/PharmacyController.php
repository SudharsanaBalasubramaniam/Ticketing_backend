<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Validator;

class PharmacyController extends Controller
{


    
   public function store_pharmacy_category(Request $request)
    {
      
      $validator = Validator::make($request->all(),[
        "name" => 'required', 
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }
    
         $data = [
             "name" => $request->name,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")  
        ];


       $category=  DB::table('pharmacy_categories')->where('name',$request->name)->first();

       if(!empty($category)){

         return response()->json([
                       "status"  => false,
                       "message"  => "This name is already added",
                    ]);

       }
       else{

        $category_new =  DB::table('pharmacy_categories')->insert($data);

        if($category_new){
              $category_data =  DB::table('pharmacy_categories')->where('name',$request->name)->first();

                    return response()->json([
                       "status"  => true,
                       "message"  => "Pharmacy category details added successfully",
                       "data" => $category_data
                    ]);

        }
        else{

              return response()->json([
                       "status"  => false,
                       "message"  => "Pharmacy category details added failed",
                       "data" => []
              ]);
        }


       }
    

    }

    
    public function pharmacy_category_list(){


    	$category = DB::table('pharmacy_categories')->get();

    	if(!$category->isEmpty()){

    	   return response()->json([
               "status"  => true,
               "message"  => "Pharmacy category list success",
               "data" => $category
           ]);
           
    	}
    	else{


    	    return response()->json([
               "status"  => false,
               "message"  => "Pharmacy category list not availavle",
               "data" => []
           ]);
           

    	}



    }


    public function update_pharmacy_category(Request $request)
    {
      
    
         $data = [
             "name" => $request->name,
             "updated_at" => date("Y-m-d H:i:s")  
        ];

        $category =  DB::table('pharmacy_categories')->where('id',$request->id)->update($data);

        if($category){
              $category_data =  DB::table('pharmacy_categories')->where('id',$request->id)->first();
                    return response()->json([
                       "status"  => true,
                       "message"  => "Pharmacy category details updated successfully",
                       "data" => $category_data
                    ]);

        }
        else{

              return response()->json([
                       "status"  => false,
                       "message"  => "Pharmacy category details updated failed",
                       "data" => []
              ]);
        }


    }

    public function delete_pharmacy_category(Request $request){

       $validator = Validator::make($request->all(),[
        "id" => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

  
    $category = DB::table('pharmacy_categories')->where('id',$request->id)->delete();

    if($category){

        return response()->json([
               "status"  => true,
               "message"  => "Pharmacy category details deleted success",

           ]);
    }
    else{
        return response()->json([
               "status"  => false,
               "message"  => "Pharmacy category details delete failed",
               "data" => []

           ]);
    }

}


 public function store_pharmacy(Request $request)
    {
      
        $validator = Validator::make($request->all(),[
        "category_id" => 'required', 
        "name" => 'required', 
        "price" => 'required', 
        // "description" => 'required'

        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

         $data = [
         	   "category_id" => $request->category_id,
             "name" => $request->name,
             "price" => $request->price,
            //  "description" => $request->description,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")  
        ];


       $pharmacy =  DB::table('pharmacy')->where('name',$request->name)->first();

       if(!empty($pharmacy)){

         return response()->json([
                       "status"  => false,
                       "message"  => "This name is already added",
                    ]);

       }
       else{

        $pharmacy_new =  DB::table('pharmacy')->insert($data);

        if($pharmacy_new){
              $pharmacy_data =  DB::table('pharmacy')->where('name',$request->name)->first();

                    return response()->json([
                       "status"  => true,
                       "message"  => "Pharmacy details added successfully",
                       "data" => $pharmacy_data
                    ]);

        }
        else{

              return response()->json([
                       "status"  => false,
                       "message"  => "Pharmacy details added failed",
                       "data" => []
              ]);
        }


       }
    

    }

     public function pharmacy_list(){


    	$pharmacy = DB::table('pharmacy')->get();

      $pharmacy = DB::table('pharmacy')
      ->select('prescription_subcategory.category_name','pharmacy.*')
      ->join('prescription_subcategory','prescription_subcategory.id','=','pharmacy.category_id')
      ->orderBy('pharmacy.id', 'DESC')
      ->get();

    	if(!$pharmacy->isEmpty()){

    	   return response()->json([
               "status"  => true,
               "message"  => "Pharmacy  list success",
               "data" => $pharmacy
           ]);
           
    	}
    	else{


    	    return response()->json([
               "status"  => false,
               "message"  => "Pharmacys list not availavle",
               "data" => []
           ]);
           

    	}



    }


 public function update_pharmacy(Request $request)
    {
      
    
         $data = [
         	  "category_id" =>$request->category_id,
              "name" => $request->name,
              "price" => $request->price,
            //   "description" => $request->description,
              "updated_at" => date("Y-m-d H:i:s")  
        ];

        $pharmacy =  DB::table('pharmacy')->where('id',$request->id)->update($data);

        if($pharmacy){
              $pharmacy_data =  DB::table('pharmacy')->where('id',$request->id)->first();
                    return response()->json([
                       "status"  => true,
                       "message"  => "Pharmacy details updated successfully",
                       "data" => $pharmacy_data
                    ]);

        }
        else{

              return response()->json([
                       "status"  => false,
                       "message"  => "Pharmacy details updated failed",
                       "data" => []
              ]);
        }


    }

    public function delete_pharmacy(Request $request){


        $validator = Validator::make($request->all(),[
        "id" => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }


    $pharmacy = DB::table('pharmacy')->where('id',$request->id)->delete();

    if($pharmacy){

        return response()->json([
               "status"  => true,
               "message"  => "Pharmacy details deleted success",

           ]);
    }
    else{
        return response()->json([
               "status"  => false,
               "message"  => "Pharmacy details delete failed",
               "data" => []
           ]);
    }

}

}
