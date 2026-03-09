<?php

namespace App\Http\Controllers\API\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\URL;
use Hash;

class StaffprofileController extends Controller
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

      // $staff_id = Auth::user()->id;
      $staff_id = $request->id;

      $image = ""; // Initialize the $image variable
      if (!empty($request->image)) {
          if (Str::contains($request->image, 'https')) {
              $image = $request->image;
          } else {
              $file_name = 'staff-' . rand() . '.' . 'png';
              $new = base64_decode($request->image);
              $success = file_put_contents(public_path() . '/staffs/' . $file_name, $new);
              $image = URL::to('/public/staffs/' . $file_name);
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
        "qualification" => $request->qualification,
        "dob" => $request->dob,
        "age" => $request->age,
        "gender" => $request->gender,
        "image" => $image,
        "updated_at" => date("Y-m-d H:i:s")
      ];



      $user = DB::table('users')->where('id', $staff_id)->update($data);

      if ($user) {
        // $user_data1 =  DB::table('users')->where('phone',$request->phone)->first();



        $user_data1 = DB::table('users')
          ->select('staff_categories.name as staff_category', 'users.id', 'users.role_id', 'role.role_name', 'users.first_name', 'users.surname', 'users.email', 'users.phone', 'users.dob', 'users.qualification', 'users.age', 'users.gender', 'users.image', 'users.address', 'users.status', 'users.created_at', 'users.updated_at')
          ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
          ->join('role', 'role.id', '=', 'users.role_id')
          ->where('users.id', $staff_id)->where('users.status', 1)->get();


        return response()->json([
          "status" => true,
          "message" => "Staff details updated successfully",
          "data" => $user_data1
        ]);

      } else {

        return response()->json([
          "status" => false,
          "message" => "Staff etails updated failed",
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


      $staff_id = $request->id;

      if ($request->password == $request->confirm_password) {


        $data = ["password" => Hash::make($request->password)];
        $update = DB::table('users')->where('id', $staff_id)->update($data);
      
        if (!empty($update)) {
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
