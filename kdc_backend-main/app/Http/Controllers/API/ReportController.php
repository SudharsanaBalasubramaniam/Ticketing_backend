<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Validate;

class ReportController extends Controller
{
    public function doctor_complete_appointments(Request $request){


    	$doctor_appointmets = DB::table('users')
    	->select('users.first_name','users.surname','appointments.appointment_id','appointments.treatment_name','appointments.appointment_date','appointments.appointment_time','appointments.appointment_status','treatmentplan.doctor_price')
    	->join('appointments','appointments.doctor_id','=','users.id')
    	->join('treatmentplan','treatmentplan.id','=','appointments.treatment_id')
    	->where('appointments.doctor_id',$request->doctor_id)
    	->where('users.status','1')
        ->where('appointments.appointment_status','Completed')
    	->get();


    	$doctor_appointmets_sum = DB::table('users')
    	->select('treatmentplan.doctor_price')
    	->join('appointments','appointments.doctor_id','=','users.id')
    	->join('treatmentplan','treatmentplan.id','=','appointments.treatment_id')
    	->where('appointments.doctor_id',$request->doctor_id)
    	->where('users.status','1')
        ->where('appointments.appointment_status','Completed')
    	->sum('treatmentplan.doctor_price');


    	if(!$doctor_appointmets->isEmpty()){

    		 return response()->json([
               "status"  => true,
               "message"  => "Doctor completed appointments list success",
               "data" => $doctor_appointmets,
               "total_amount" => $doctor_appointmets_sum

           ]);

    	}
    	else{
    		return response()->json([
               "status"  => false,
               "message"  => "Doctor completed appointments list is empty",
               "data" => []
           ]);
    		
    	}

    }
}
