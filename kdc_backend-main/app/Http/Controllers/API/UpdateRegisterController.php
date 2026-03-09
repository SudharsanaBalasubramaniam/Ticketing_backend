<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class UpdateRegisterController extends Controller
{
    public function update_registration(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required',
            "surname" => 'required|string|max:255',
            "address" => 'required|string|max:255',
            "dob" => 'required',
            "age" => 'required',
            "gender" => 'required|string|max:255',
            "image" => 'required',
            "qualification" => 'required|string|max:255',
            "profession" => 'required|string|max:255',
            "primary_contact_name" => 'required|string|max:255',
            "relationship" => 'required|string|max:255',
            "check1" => 'required|string|max:255',
            "check2" => 'required|string|max:255',
            "check3" => 'required|string|max:255',
            "check4" => 'required|string|max:255',
            "check5" => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {

            $user_id = $request->id;

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
                "surname" => $request->surname,
                "address" => $request->address,
                "dob" => $request->dob,
                "age" => $request->age,
                "gender" => $request->gender,
                "image" => $request->image,
                "profession" => $request->profession,
                "qualification" => $request->qualification,
                "primary_contact_name" => $request->primary_contact_name,
                "relationship" => $request->relationship,
                "check1" => $request->check1,
                "check2" => $request->check2,
                "check3" => $request->check3,
                "check4" => $request->check4,
                "check5" => $request->check5,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
            ];

            $update = DB::table('members')->where('id', $user_id)->update($data);



            if ($update) {

                $user = DB::table('members')->where('id', $user_id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "User status updated success",
                    "data" => $user
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "User status updated failed",
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }

    // $ran_id = array();
    // foreach ($users as $users_key => $users_value) {

    // if($users_value->registration_number!=NULL){

    //   $ran_id[] = $users_value->registration_number;

    // }
    // }


    // if(!empty($ran_id)){
    //  $user_registration_number = max($ran_id)+1;
    //  $registration_number = $user_registration_number;
    // }
    // else{
    //    $registration_number ='10001'; 
    // }

    // }
    // else{

    //    $registration_number ='10001'; 

    // }


    //     $role = DB::table('role')->where('role_name','Patient')->first();

    //  $data = [
    //      "registration_number" => $registration_number,
    //  	   "role_id" => $role->id,
    //      "first_name" => $request->first_name,
    //      "surname" => $request->surname,
    //      "email" => $request->email,
    //      "phone" => $request->phone,
    //      "address" => $request->address,
    //      // "password" => Hash::make($request->password),
    //      "password" => Hash::make('Kirthigadentelcare'),
    //      "dob" => $request->dob,
    //      "age" => $request->age,
    //      "gender" => $request->gender,
    //      "image" => $request->image,
    //      "profession" => $request->profession,
    //      "primary_contact_name" => $request->primary_contact_name,
    //      "relationship" => $request->relationship,
    //      "verify" => date("Y-m-d H:i:s"),
    //      "check1" => $request->check1,
    //      "check2" => $request->check2,
    //      "check3" => $request->check3,
    //      "check4" => $request->check4,
    //      "check5" => $request->check5,
    //      "status" => 1,
    //      "created_at" => date("Y-m-d H:i:s"),
    //      "updated_at" => date("Y-m-d H:i:s")
    // ];


    //   $user_data =  DB::table('users')->where('phone',$request->phone)->first();

    //   if(!empty($user_data)){

    //     return response()->json([
    //                "status"  => false,
    //                "message"  => "The mobile number is already taken",

    //             ]);

    //   }
    //   else{

    //         $user =  DB::table('users')->insert($data);

    //       if($user){
    //               $user_data1 =  DB::table('users')->where('phone',$request->phone)->first();

    //                     return response()->json([
    //                        "status"  => true,
    //                        "message"  => "Patient registration successfully",
    //                        "data" => $user_data1
    //                     ]);

    //         }
    //         else{

    //               return response()->json([
    //                        "status"  => false,
    //                        "message"  => "Patient registration failed",
    //                        "data" => []
    //               ]);
    //         }

    //   }

    //     }
}
