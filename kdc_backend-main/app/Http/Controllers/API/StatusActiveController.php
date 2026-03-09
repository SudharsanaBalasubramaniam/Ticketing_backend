<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatusActiveController extends Controller
{

    public function updatestatus_diagnosis(Request $request){

        $validator = Validator::make($request->all(),[
           "id" => 'required',
           "status" => 'required|regex:/^[01]$/', 
         ]);
 
         if($validator->fails()){
             return response()->json($validator->errors());       
         }
 
         try{
 
             $user_id = $request->id;
 
             $data = [
                "status" => $request->status,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")
            ];
 
             $update = DB::table('diagnosis')->where('id',$user_id)->update($data);
             if($update){
 
                 $user =  DB::table('diagnosis')->where('id',$user_id)->first();
 
                 return response()->json([
                "status"  => true,
                "message"  => "User status updated success",
                "data" => $user
            ]);
  
             }
             else{
              return response()->json([
                "status"  => false,
                "message"  => "User status updated failed",
            ]);
 
             }
 
 
         } catch (\Exception $e) {
 
         \Log::error('Exception occurred: ' . $e->getMessage());
         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }


    public function updatestatus_diagnosistype(Request $request){

        $validator = Validator::make($request->all(),[
           "id" => 'required',
           "status" => 'required|regex:/^[01]$/', 
         ]);
 
         if($validator->fails()){
             return response()->json($validator->errors());       
         }
 
         try{
 
             $user_id = $request->id;
 
             $data = [
                "status" => $request->status,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")
            ];
 
             $update = DB::table('diagnosis_type')->where('id',$user_id)->update($data);
             if($update){
 
                 $user =  DB::table('diagnosis_type')->where('id',$user_id)->first();
 
                 return response()->json([
                "status"  => true,
                "message"  => "User status updated success",
                "data" => $user
            ]);
  
             }
             else{
              return response()->json([
                "status"  => false,
                "message"  => "User status updated failed",
            ]);
 
             }
 
 
         } catch (\Exception $e) {
 
         \Log::error('Exception occurred: ' . $e->getMessage());
         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
     }



     
     public function updatestatus_category(Request $request){

        $validator = Validator::make($request->all(),[
           "id" => 'required',
           "status" => 'required|regex:/^[01]$/', 
         ]);
 
         if($validator->fails()){
             return response()->json($validator->errors());       
         }
 
         try{
 
             $user_id = $request->id;
 
             $data = [
                "status" => $request->status,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")
            ];
 
             $update = DB::table('category')->where('id',$user_id)->update($data);
             if($update){
 
                 $user =  DB::table('category')->where('id',$user_id)->first();
 
                 return response()->json([
                "status"  => true,
                "message"  => "User status updated success",
                "data" => $user
            ]);
  
             }
             else{
              return response()->json([
                "status"  => false,
                "message"  => "User status updated failed",
            ]);
 
             }
 
 
         } catch (\Exception $e) {
 
         \Log::error('Exception occurred: ' . $e->getMessage());
         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }

     }

     public function updatestatus_subcategory(Request $request){

        $validator = Validator::make($request->all(),[
           "id" => 'required',
           "status" => 'required|regex:/^[01]$/', 
         ]);
 
         if($validator->fails()){
             return response()->json($validator->errors());       
         }
 
         try{
 
             $user_id = $request->id;
 
             $data = [
                "status" => $request->status,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")
            ];
 
             $update = DB::table('subcategory')->where('id',$user_id)->update($data);
             if($update){
 
                 $user =  DB::table('subcategory')->where('id',$user_id)->first();
 
                 return response()->json([
                "status"  => true,
                "message"  => "User status updated success",
                "data" => $user
            ]);
  
             }
             else{
              return response()->json([
                "status"  => false,
                "message"  => "User status updated failed",
            ]);
 
             }
 
 
         } catch (\Exception $e) {
 
         \Log::error('Exception occurred: ' . $e->getMessage());
         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
     }


     public function updatestatus_treatmentmethod(Request $request){

        $validator = Validator::make($request->all(),[
           "id" => 'required',
           "status" => 'required|regex:/^[01]$/', 
         ]);
 
         if($validator->fails()){
             return response()->json($validator->errors());       
         }
 
         try{
 
             $user_id = $request->id;
 
             $data = [
                "status" => $request->status,
             "created_at" => date("Y-m-d H:i:s"),
             "updated_at" => date("Y-m-d H:i:s")
            ];
 
             $update = DB::table('treatment_methods')->where('id',$user_id)->update($data);
             if($update){
 
                 $user =  DB::table('treatment_methods')->where('id',$user_id)->first();
 
                 return response()->json([
                "status"  => true,
                "message"  => "User status updated success",
                "data" => $user
            ]);
  
             }
             else{
              return response()->json([
                "status"  => false,
                "message"  => "User status updated failed",
            ]);
 
             }
 
 
         } catch (\Exception $e) {
 
         \Log::error('Exception occurred: ' . $e->getMessage());
         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
     }


}
