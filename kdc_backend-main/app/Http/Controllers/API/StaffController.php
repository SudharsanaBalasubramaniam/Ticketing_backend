<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DB;
use URL;
use Illuminate\Support\Str;


class StaffController extends Controller
{

    public function store_staff_category(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "name" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $data = [
            "name" => $request->name,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];


        $staff_category_name = DB::table('staff_categories')->where('name', $request->name)->first();

        if (!empty($staff_category_name)) {

            return response()->json([
                "status" => false,
                "message" => "This name is already added",
            ]);

        } else {

            $staff_category = DB::table('staff_categories')->insert($data);

            if ($staff_category) {
                $staff_category_data = DB::table('staff_categories')->where('name', $request->name)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Staff category details added successfully",
                    "data" => $staff_category_data
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Staff category details added failed",
                    "data" => []
                ]);
            }


        }


    }


    public function staff_category_list()
    {

        $staff_category = DB::table('staff_categories')->get();

        if (!$staff_category->isEmpty()) {

            return response()->json([
                "status" => true,
                "message" => "Staff category details success!",
                "data" => $staff_category

            ]);

        } else {
            return response()->json([
                "status" => false,
                "message" => "Staff category details failed!",

            ]);

        }

    }


    public function update_staff_category(Request $request)
    {


        $data = [
            "name" => $request->name,
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $staff_category = DB::table('staff_categories')->where('id', $request->id)->update($data);

        if ($staff_category) {
            $staff_category_data = DB::table('staff_categories')->where('id', $request->id)->first();
            return response()->json([
                "status" => true,
                "message" => "Staff category details updated successfully",
                "data" => $staff_category_data
            ]);

        } else {

            return response()->json([
                "status" => false,
                "message" => "Staff category details updated failed",
                "data" => []
            ]);
        }


    }


    public function delete_staff_category(Request $request)
    {


        $staff_category = DB::table('staff_categories')->where('id', $request->id)->delete();

        if ($staff_category) {

            return response()->json([
                "status" => true,
                "message" => "Staff category details deleted success",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Staff category details delete failed",
                "data" => []

            ]);
        }

    }

    public function store_staff(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "first_name" => 'required',
            "phone" => 'required',
            "aadhar_number" => 'required|unique:users',
           
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        try {
            // if(!empty($request->image)){

            //        $file_name = 'staff-'.rand().'.'.'png';
            //         $new = base64_decode($request->image);
            //         $success = file_put_contents(public_path().'/staffs/'.$file_name, $new);
            //         $image = URL::to('/public/staffs/'.$file_name);
            //       }

            //     if(empty($image)){

            //       $image = "";

            //     }

            $role = DB::table('role')->where('role_name', 'Staff')->first();
              // Get the latest doctor number from the database
    
        
           $latestDoctor = DB::table('users')->where('role_id', $role->id)->orderBy('id', 'desc')->first();
        $doctorNumber = $latestDoctor ? intval(substr($latestDoctor->employee_number, 5)) + 1 : 1;
        $doctorNumberFormatted = str_pad($doctorNumber, 4, '0', STR_PAD_LEFT);
        $doctorNumberWithPrefix = 'KDC-S' . $doctorNumberFormatted;


            $data = [
                "role_id" => $role->id,
                "staff_category_id" => $request->staff_category_id,
                "first_name" => $request->first_name,
                "surname" => $request->surname,
                "aadhar_number" => $request->aadhar_number,
                "email" => $request->email,
                "phone" => $request->phone,
                "address" => $request->address,
                "qualification" => $request->qualification,
                "password" => Hash::make($request->password),
                "confirm_password" => Hash::make($request->confirm_password),
                "dob" => $request->dob,
                "age" => $request->age,
                "gender" => $request->gender,
                //  "image" => $image,
                "verify" => date("Y-m-d H:i:s"),
                "status" => 1,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                "employee_number" => $doctorNumberWithPrefix,
            ];

            $user = DB::table('users')->insert($data);


            if ($user) {
                $user_data = DB::table('users')
                ->select(
                    'staff_categories.name as staff_category',
                     'staff_categories.id as staff_category_id',
                    'users.staff_category_id',
                    'users.qualification',
                    'users.id',
                    'users.employee_number',
                    'users.aadhar_number',
                    'users.role_id',
                    'role.role_name',
                    'users.first_name',
                    'users.surname',
                    'users.email',
                    'users.phone',
                    'users.dob',
                    'users.age',
                    'users.gender',
                    'users.profession',
                    'users.image',
                    'users.address',
                    'users.status',
                    'users.created_at',
                    'users.updated_at'
                )
             
                ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
                ->join('role', 'role.id', '=', 'users.role_id')
                ->where('users.phone', $request->phone)
                ->first();

                return response()->json([
                    "status" => true,
                    "message" => "staff details added successfully",
                    "data" => $user_data
                ]);
            } else {
                $updateUserTable = DB::table('users')->where('id', $user->id)->update($data);
                $user_data = DB::table('users')
                ->select(
                    'staff_categories.name as staff_category',
                     'staff_categories.id as staff_category_id',
                    'users.staff_category_id',
                    'users.qualification',
                    'users.id',
                    'users.employee_number',
                    'users.aadhar_number',
                    'users.role_id',
                    'role.role_name',
                    'users.first_name',
                    'users.surname',
                    'users.email',
                    'users.phone',
                    'users.dob',
                    'users.age',
                    'users.gender',
                    'users.profession',
                    'users.image',
                    'users.address',
                    'users.status',
                    'users.created_at',
                    'users.updated_at'
                )
             
                ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
                ->join('role', 'role.id', '=', 'users.role_id')
                ->where('users.phone', $request->phone)
                ->first();

                return response()->json([
                    "status" => true,
                    "message" => "staff details added successfully",
                    "data" => $user_data
                ]);
            }


        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
        }
    }




            // $user_data =  DB::table('users')->where('phone',$request->phone)->first();

            // if(!empty($user_data)){

            //   return response()->json([
            //              "status"  => false,
            //              "message"  => "The mobile number is already taken",

            //           ]);

            // }
            // else{

            //   if($request->password == $request->confirm_password){
            //      $user =  DB::table('users')->insert($data);

            //     if($user){
            //             // $user_data1 =  DB::table('users')->where('phone',$request->phone)->first();



            //         $user_data1 = DB::table('users')
            //         ->select('staff_categories.name as staff_category','users.id','users.role_id','role.role_name','users.first_name','users.surname','users.email','users.phone','users.dob','users.age','users.gender','users.image','users.address','users.status','users.created_at','users.updated_at') 
            //         ->join('staff_categories','staff_categories.id','=','users.staff_category_id')
            //         ->join('role','role.id','=','users.role_id')
            //         ->where('phone',$request->phone)->where('users.status',1)->get();


            //                   return response()->json([
            //                      "status"  => true,
            //                      "message"  => "Staff registration successfully",
            //                      "data" => $user_data1
            //                   ]);

            //    }
            //       else{

            //             return response()->json([
            //                      "status"  => false,
            //                      "message"  => "Staff registration failed",
            //                      "data" => []
            //             ]);
            //       }
            //   }
            //   else{

            //      return response()->json([
            //              "status"  => false,
            //              "message"  => "Password and confirm password not matched"
            //     ]);

            //   }


            // }


 public function staff_list()
    {

        $role = DB::table('role')->where('role_name', 'Staff')->first();


        $staffs = DB::table('users')
            ->select(
                'staff_categories.name as staff_category',
                'staff_categories.id as staff_category_id',
                'users.staff_category_id',
                'users.qualification',
                'users.id',
                'users.employee_number',
                'users.aadhar_number',
                'users.role_id',
                'role.role_name',
                'users.first_name',
                'users.surname',
                'users.email',
                'users.phone',
                'users.dob',
                'users.age',
                'users.gender',
                'users.image',
                'users.address',
                'users.status',
                'users.created_at',
                'users.updated_at'
            )
            ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
            ->join('role', 'role.id', '=', 'users.role_id')
            ->where('users.role_id', $role->id)
            // ->where('users.status','=', 1)
            ->get();


        // $staffs = DB::table('users')->where('role_id',$role->id)->where('status',1)->get();

        if (!$staffs->isEmpty()) {
            return response()->json([
                "status" => true,
                "message" => "Staff details success!",
                "data" => $staffs

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Staff details not available",
                "data" => []
            ]);

        }

    }

    public function delete_staff(Request $request)
    {

        $role = DB::table('role')->where('role_name', 'Staff')->first();
        $staff = DB::table('users')->where('id', $request->id)->where('role_id', $role->id)->update([
            'status' => $request->isChecked ? 1:0
          ]);

        if ($staff) {

            return response()->json([
                "status" => true,
                "message" => "Staff details deleted success",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Staff details delete failed",
                "data" => []

            ]);
        }

    }
    // public function staff_list()
    // {

    //     $role = DB::table('role')->where('role_name', 'Staff')->first();


    //     $staffs = DB::table('users')
    //         ->select(
    //             'staff_categories.name as staff_category',
    //             'staff_categories.id as staff_category_id',
    //             'users.staff_category_id',
    //             'users.qualification',
    //             'users.id',
    //             'users.employee_number',
    //             'users.aadhar_number',
    //             'users.role_id',
    //             'role.role_name',
    //             'users.first_name',
    //             'users.surname',
    //             'users.email',
    //             'users.phone',
    //             'users.dob',
    //             'users.age',
    //             'users.gender',
    //             'users.image',
    //             'users.address',
    //             'users.status',
    //             'users.created_at',
    //             'users.updated_at'
    //         )
    //         ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
    //         ->join('role', 'role.id', '=', 'users.role_id')
    //         ->where('users.role_id', $role->id)->get();


    //     // $staffs = DB::table('users')->where('role_id',$role->id)->where('status',1)->get();

    //     if (!$staffs->isEmpty()) {
    //         return response()->json([
    //             "status" => true,
    //             "message" => "Staff details success!",
    //             "data" => $staffs

    //         ]);
    //     } else {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "Staff details not available",
    //             "data" => []
    //         ]);

    //     }

    // }

    // public function delete_staff(Request $request)
    // {

    //     $role = DB::table('role')->where('role_name', 'Staff')->first();
    //     $staff = DB::table('users')->where('id', $request->id)->where('role_id', $role->id)->delete();

    //     if ($staff) {

    //         return response()->json([
    //             "status" => true,
    //             "message" => "Staff details deleted success",

    //         ]);
    //     } else {
    //         return response()->json([
    //             "status" => false,
    //             "message" => "Staff details delete failed",
    //             "data" => []

    //         ]);
    //     }

    // }



    public function update_staff(Request $request)
    {

         $validator = Validator::make($request->all(), [
            "phone" => 'unique:users,phone,' . $request->id,
            "aadhar_number" => 'unique:users,aadhar_number,' . $request->id
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        
 
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

            "staff_category_id" => $request->staff_category_id,
            "aadhar_number" => $request->aadhar_number,
            "qualification" => $request->qualification, 
            "first_name" => $request->first_name,
            "surname" => $request->surname,
            "email" => $request->email,
            "phone" => $request->phone,
            "address" => $request->address,
            "dob" => $request->dob,
            "age" => $request->age,
            "gender" => $request->gender,
            "image" => $image,
            "contact_mobile"=> $request->contact_mobile,
            "relationship" => $request->relationship,
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $user = DB::table('users')->where('id', $request->id)->update($data);

        if ($user) {
            $user_data = DB::table('users')
            ->select(
                'staff_categories.name as staff_category',
                'users.staff_category_id',
                'users.qualification',
                'users.id',
                'users.role_id',
                'role.role_name',
                'users.first_name',
                'users.surname',
                'users.email',
                'users.phone',
                'users.dob',
                'users.age',
                'users.gender',
                'users.contact_mobile',
                'users.profession',
                'users.image',
                'users.address',
                'users.status',
                'users.created_at',
                'users.updated_at'
            )
         
            ->join('staff_categories', 'staff_categories.id', '=', 'users.staff_category_id')
            ->join('role', 'role.id', '=', 'users.role_id')
            ->where('users.phone', $request->phone)
            ->first();

            return response()->json([
                "status" => true,
                "message" => "Staff details updated successfully",
                "data" => $user_data
            ]);

        } else {

            return response()->json([
                "status" => false,
                "message" => "Staff details updated failed",
                "data" => []
            ]);
        }



    }


}