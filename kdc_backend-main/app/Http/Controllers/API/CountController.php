<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;

class CountController extends Controller
{
   public function counts(){

   
  try{
   	  $role = DB::table('role')->where('role_name','Patient')->first();
      $currentMonth = date('m');
     

             $new_patient = DB::table('users')
          ->whereRaw('MONTH(created_at) = ?',[$currentMonth])
          ->where('role_id',$role->id)
          ->where('status',1)->count();


             $old_patient = DB::table('users')
          ->whereRaw('MONTH(created_at) < ?',[$currentMonth])
          ->where('role_id',$role->id)
          ->where('status',1)->count();


       $total_patient = DB::table('users')
          ->where('role_id',$role->id)
          ->where('status',1)->count();

           $ongoing_appointments = DB::table('appointments')
      ->where('appointment_status','=',"Inprogress")->count();


      $completed_appointments = DB::table('appointments')
      ->where('appointment_status','=',"Completed")->count();
     


$data = [
	"new_patients" => $new_patient,
	"old_patients" => $old_patient,
	"total_patients" => $total_patient,
	"ongoing_appointments" => $ongoing_appointments,
	"completed_appointments" => $completed_appointments
    ];

  return response()->json([
               "status"  => true,
               "message"  => "All counts data success",
               "data" => $data
           ]);



   
} catch (\Exception $e) {

   \Log::error('Exception occurred: ' . $e->getMessage());
   return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
}
}
}