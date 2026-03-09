<?php

namespace App\Http\Controllers\API\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class PatientprofileController extends Controller
{

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
      ]);

      if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()->all()], 422);
      }



      $patient_id = $request->id;


      $image = ""; // Initialize the $image variable
      if (!empty($request->image)) {
          if (Str::contains($request->image, 'https')) {
              $image = $request->image;
          } else {
        $file_name = 'patient-' . rand() . '.' . 'png';
        $new = base64_decode($request->image);
        $success = file_put_contents(public_path() . '/patients/' . $file_name, $new);
        $image = URL::to('/public/patients/' . $file_name);
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
        "contact_mobile"=> $request->contact_mobile,
        "image" => $image,
        "updated_at" => date("Y-m-d H:i:s")
      ];


      $user =  DB::table('users')->where('id', $patient_id)->update($data);

      if ($user) {
        // $user_data1 =  DB::table('users')->where('phone',$request->phone)->where('status',1)->first();

        $user_data1 = DB::table('users')
          ->select('users.id', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.age', 'users.gender', 'users.image', 'users.address',  'users.contact_mobile', 'users.profession', 'users.primary_contact_name', 'users.relationship', 'users.check1', 'users.check2', 'users.check3', 'users.check4', 'users.check5', 'users.status', 'users.created_at', 'users.updated_at')
          ->join('role', 'role.id', '=', 'users.role_id')
          ->where('users.phone', $request->phone)->where('users.id', $patient_id)->where('users.status', 1)->get();

        return response()->json([
          "status"  => true,
          "message"  => "Patient details updated successfully",
          "data" => $user_data1
        ]);
      } else {

        return response()->json([
          "status"  => false,
          "message"  => "Patient details updated failed",
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
        "id" => 'required',
        "password" => 'required',
        "confirm_password" => 'required',
      ]);

      if ($validator->fails()) {
        return response()->json($validator->errors());
      }


      $patient_id = $request->id;

      if ($request->password == $request->confirm_password) {


        $data = ["password" => Hash::make($request->password)];
        $update = DB::table('users')->where('id', $patient_id)->update($data);
        if ($update) {
          return response()->json([
            "status"  => true,
            "message"  => "Password updated successfully",
          ]);
        } else {
          return response()->json([
            "status"  => false,
            "message"  => "Password updated failed"
          ]);
        }
      } else {

        return response()->json([
          "status"  => false,
          "message"  => "Password and confirm password not matched"
        ]);
      }
    } catch (\Exception $e) {

      \Log::error('Exception occurred: ' . $e->getMessage());
      return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    }
  }
}
