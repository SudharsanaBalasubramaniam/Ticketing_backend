<?php

namespace App\Http\Controllers\API\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\URL;
use Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{

  public function doctor_earning(Request $request)
  {
    try {


      $doctor_id = $request->id;
      $doctor_earning = DB::table('treatment_plan_method')
        ->select(

          DB::raw('DISTINCT treatment_plan_method.id'),

          'treatment_plan_method.treatment_procedure_id',
          'treatment_plan_procedure.treatment_procedure',
          'treatment_plan_method.treatment_method',
          'treatment_plan_method.method_price',
          'treatment_plan_method.doctor_price',
          'treatment_plan_method.created_at',
          'treatment_plan_method.updated_at',
          'doctor.first_name as doctor_name',
          'appointments.appointment_date',
          'appointments.id as app_id',
          'appointments.appointment_time',
          'appointments.patient_id',
          'users.first_name as patient_name',
          'appointments.treatment_id',
          'appointments.treatment_name'
        )
        ->join('users as doctor', 'doctor.id', '=', 'treatment_plan_method.doctor_id')
        ->join('treatment_plan_procedure', 'treatment_plan_procedure.id', '=', 'treatment_plan_method.treatment_procedure_id')
        ->join('appointments', 'appointments.method_id', '=', 'treatment_plan_method.id')
        ->join('users', 'users.id', '=', 'appointments.patient_id')
        ->where('doctor.id', $doctor_id)
        ->where('appointments.doctor_id', $doctor_id)

        ->get();

      if ($doctor_earning->isNotEmpty()) {
        $total_earning = DB::table('treatment_plan_method')
          ->where('doctor_id', $doctor_id)
          ->sum('doctor_price');

        return response()->json([
          "status" => true,
          "message" => "Doctor earning fetched successfully",
          "data" => [
            "total_earning" => $total_earning,
            "doctor_earning" => $doctor_earning

          ]
        ]);
      } else {
        return response()->json([
          "status" => false,
          "message" => "No earning data found for the doctor",
        ]);
      }
    } catch (\Exception $e) {
      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.']);
    }
  }
  public function treatment_plan_list(Request $request)
  {

    try {

      // $doctor_id = Auth::user()->id;
      $doctor_id = $request->id;


      $treatment_plan_mapping_list = DB::table('treatment_plan_mapping')
        ->select(

          'treatment_plan_mapping.*',

          'users.first_name as patient_name',
          'doctor.first_name as doctor_name'
        )
        // ->join('diagnosis', 'diagnosis.id', '=', 'treatment_plan_mapping.diagnosis_name')
        // ->join('diagnosis_type', 'diagnosis_type.id', '=', 'treatment_plan_mapping.diagnosis_type')
        ->join('users', 'users.id', '=', 'treatment_plan_mapping.patient_id')
        ->join('users as doctor', 'doctor.id', '=', 'treatment_plan_mapping.doctor_id')
        ->join('appointments', 'appointments.doctor_id', '=', 'treatment_plan_mapping.doctor_id')
        ->where('doctor.id', $doctor_id)
        ->get();

      if ($treatment_plan_mapping_list) {
        $treatment_plan_mapping_list = DB::table('treatment_plan_mapping')->where('doctor_id', $doctor_id)->first();


        return response()->json([
          "status" => true,
          "message" => "treatment plan mapping success",
          "data" => $treatment_plan_mapping_list
        ]);
      } else {
        return response()->json([
          "status" => false,
          "message" => "treatment plan mapping failed",
        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }




  public function profile(Request $request)
  {

    try {

      $validator = Validator::make($request->all(), [
        "first_name" => "required",
        "surname" => "required",
        "email" => "required",
        "phone" => "required",
        "address" => "required",
        "dob" => "required",
        "age" => "required",
        "gender" => "required",
        "qualification" => "required"
      ]);

      if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
      }


      $doctor_id = $request->id;

      $image = ""; // Initialize the $image variable
      if (!empty($request->image)) {
          if (Str::contains($request->image, 'https')) {
              $image = $request->image;
          } else {
              $file_name = 'doctor-' . rand() . '.' . 'png';
              $new = base64_decode($request->image);
              $success = file_put_contents(public_path() . '/doctors/' . $file_name, $new);
              $image = URL::to('/public/doctors/' . $file_name);
        }
      }
      if (empty($image)) {
        $image = "";
      }

      $data = [

        "first_name" => $request->first_name,
        "surname" => $request->surname,
        "email" => $request->email,
        "phone" => $request->phone,
        "address" => $request->address,
        "dob" => $request->dob,
        "age" => $request->age,
        "gender" => $request->gender,
        "image" => $image,
        "qualification" => $request->qualification,
        "updated_at" => date("Y-m-d H:i:s")
      ];



      $update_user = DB::table('users')->where('id', $doctor_id)->update($data);

      if ($update_user) {

        $role = DB::table('role')->where('role_name', 'Doctor')->first();
        $doctors = DB::table('users')
          ->select('doctor_departments.title as department_name', 'users.id', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.qualification', 'users.status', 'users.created_at', 'users.updated_at')
          ->join('doctor_departments', 'doctor_departments.id', '=', 'users.department_id')
          ->join('role', 'role.id', '=', 'users.role_id')
          ->where('users.role_id', $role->id)->where('users.id', $doctor_id)->get();

        return response()->json([
          "status" => true,
          "message" => "Profile details updated successfully",
          "data" => $doctors
        ]);
      } else {

        return response()->json([
          "status" => false,
          "message" => "Profile details updated failed",
          "data" => []
        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }
  public function convertToBase64(Request $request)
  {
    $url = $request->networkImage;

    $response = Http::get($url);

    if ($response->successful()) {
      $base64Data = base64_encode($response->body());

      return response()->json([
        'status' => true,
        'message' => 'PDF converted to base64 successfully',
        'data' => $base64Data,
      ]);
    } else {
      return response()->json([
        'status' => false,
        'message' => 'Failed to convert PDF to base64',
        'error' => $response->status(),
      ], $response->status());
    }
  }

  //admin profile
  public function admin_profile(Request $request)
  {

    try {

      $validator = Validator::make($request->all(), [
        "id" => "required",
        "first_name" => "required",
        "surname" => "required",
        "email" => "required",
        "phone" => 'unique:users,phone,' . $request->id,
        "aadhar_number" => 'unique:users,aadhar_number,' . $request->id,
        "address" => "required",
        "dob" => "required",
        "age" => "required",
        "gender" => "required",
        "qualification" => "required"
      ]);

      if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
      }

      $image = ""; // Initialize the $image variable
      if (!empty($request->image)) {
          if (Str::contains($request->image, 'https')) {
              $image = $request->image;
          } else {
          $file_name = 'admin-' . rand() . '.' . 'png';
          $new = base64_decode($request->image);
          $success = file_put_contents(public_path() . '/admin/' . $file_name, $new);
          $image = URL::to('/public/admin/' . $file_name);
        }
      }
      if (empty($image)) {
        $image = "";
      }

      $id = $request->id;
      $data = [
        "first_name" => $request->first_name,
        "surname" => $request->surname,
        "email" => $request->email,
        "phone" => $request->phone,
        "address" => $request->address,
        "dob" => $request->dob,
        "age" => $request->age,
        "gender" => $request->gender,
        "image" => $image,
        "qualification" => $request->qualification,
        "aadhar_number" => $request->aadhar_number,
        "relationship" => $request->relationship,
        "contact_mobile"=> $request->contact_mobile,
        "updated_at" => date("Y-m-d H:i:s")
      ];



      $update_user = DB::table('users')->where('id', $id)->update($data);

      if ($update_user) {

        $admin = DB::table('users')->where('id', $id)->first();


        return response()->json([
          "status" => true,
          "message" => "Profile details updated successfully",
          "data" => $admin
        ]);
      } else {

        return response()->json([
          "status" => false,
          "message" => "Profile details updated failed",
          "data" => []
        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }

  public function change_password(Request $request)
  {

    try {

      $validator = Validator::make($request->all(), [
        "doctor_id" => 'required',
        "password" => 'required',
        "confirm_password" => 'required',
      ]);

      if ($validator->fails()) {
        return response()->json($validator->errors());
      }


      $doctor_id = $request->id;

      if (!empty($request->password == $request->confirm_password)) {

        $data = ["password" => Hash::make($request->password), "confirm_password" => Hash::make($request->confirm_password)];
        $upd_pwd = DB::table('users')->where('id', $doctor_id)->update($data);

        if ($upd_pwd) {
          return response()->json([
            "status" => true,
            "message" => "Password updated successfully",
          ]);
        } else {
          return response()->json([
            "status" => false,
            "message" => "Password updated failed"
          ]);
        }
      } else {

        return response()->json([
          "status" => false,
          "message" => "Password and confirm password not matched"
        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }
}
