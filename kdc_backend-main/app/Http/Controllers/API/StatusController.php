<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Illuminate\Support\Facades\Validator;

class StatusController extends Controller
{
   
    public function active_inactive(Request $request){

       $validator = Validator::make($request->all(),[
          "id" => 'required',
          "status" => 'required', 
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

    	try{

    		$user_id = $request->id;

    		$data = ["status" => $request->status];

    		$update = DB::table('members')->where('id',$user_id)->update($data);
    		if($update){

    		    $user =  DB::table('members')->where('id',$user_id)->first();

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
    
     public function update_employee_number(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "employee_number" =>'unique:users,employee_number,' . $request->id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {

            $user_id = $request->id;

            $data = ["employee_number" => $request->employee_number];

            $update = DB::table('users')->where('id', $user_id)->update($data);
            if ($update) {

                $user =  DB::table('users')->where('id', $user_id)->first();

                return response()->json([
                    "status"  => true,
                    "message"  => "User employee_number updated success",
                    "data" => $user
                ]);
            } else {
                return response()->json([
                    "status"  => false,
                    "message"  => "User employee_number updated failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.']);
        }
    }


}
