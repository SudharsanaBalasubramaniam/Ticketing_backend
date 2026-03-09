<?php

namespace App\Http\Controllers\API\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;

class PatientdataController extends Controller
{
    
    public function patient_info(){

    	try{

    		$patient_id = Auth::user()->id;


         $user_data1 = DB::table('users')
          ->select('users.id','users.role_id','role.role_name','users.first_name','users.surname','users.email','users.phone','users.dob','users.age','users.gender','users.image','users.address','users.profession','users.primary_contact_name','users.relationship','users.check1','users.check2','users.check3','users.check4','users.check5','users.status','users.created_at','users.updated_at')
          ->join('role','role.id','=','users.role_id')
          ->where('users.id',$patient_id)->where('users.status',1)->get();



		if(!$user_data1->isEmpty()){
			 return response()->json([
		                       "status"  => true,
		                       "message"  => "Patient details successfully",
		                       "data" => $user_data1
		                    ]);

		}
		else{
			 return response()->json([
		                       "status"  => false,
		                       "message"  => "Patient details failed",
		                       "data" => []
		                    ]);
		}
                   



    	}
    	catch (\Exception $e) {
        \Log::error('Exception occurred: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
       }  


    }
}
