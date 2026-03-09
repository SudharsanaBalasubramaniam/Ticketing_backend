<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{


    public function store_inventory_category(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "name" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $data = [
            "name" => $request->name,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];


        $inventory_categories = DB::table('inventory_categories')->where('name', $request->name)->first();

        if (!empty($inventory_categories)) {

            return response()->json([
                "status" => false,
                "message" => "This name is already added",
            ]);
        } else {

            $inventory_categories = DB::table('inventory_categories')->insert($data);

            if ($inventory_categories) {
                $inventory_categories_data = DB::table('inventory_categories')->where('name', $request->name)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Inventory category details added successfully",
                    "data" => $inventory_categories_data
                ]);
            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Inventory category details added failed",
                    "data" => []
                ]);
            }
        }
    }
    public function inventory_category_list()
    {


        $inventory_categories = DB::table('inventory_categories')->get();

        if (!$inventory_categories->isEmpty()) {

            return response()->json([
                "status" => true,
                "message" => "Inventory category list success",
                "data" => $inventory_categories
            ]);
        } else {

            return response()->json([
                "status" => false,
                "message" => "Inventory category list not availavle",
                "data" => []
            ]);
        }
    }

    public function update_inventory_category(Request $request)
    {


        $data = [

            "name" => $request->name,
            "updated_at" => date("Y-m-d H:i:s")
        ];

        $category = DB::table('inventory_categories')->where('id', $request->id)->update($data);

        if ($category) {
            $category_data = DB::table('inventory_categories')->where('id', $request->id)->first();
            return response()->json([
                "status" => true,
                "message" => "Inventory category details updated successfully",
                "data" => $category_data
            ]);
        } else {

            return response()->json([
                "status" => false,
                "message" => "Inventory category updated failed",
                "data" => []
            ]);
        }
    }

    public function delete_inventory_category(Request $request)
    {


        $validator = Validator::make($request->all(), [
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }


        $category = DB::table('inventory_categories')->where('id', $request->id)->delete();

        if ($category) {

            return response()->json([
                "status" => true,
                "message" => "Inventory category details deleted success",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Inventory category details delete failed",
                "data" => []
            ]);
        }
    }





    public function store_inventory(Request $request)
    {
        $data = [
            "category_id" => $request->category_id,
            "material_name" => $request->material_name,
            "make" => $request->make,
            "stock_in" => $request->stock_in,
            "stock_out" => "0",
            "price" => $request->price,
            "status" => $request->status,
            "condition_status" => $request->condition_status,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];


        $inventory = DB::table('inventory')->where('category_id', $request->category_id)->where('material_name', $request->material_name)->first();

        if (!empty($inventory)) {

            return response()->json([
                          "status"  => false,
                          "message"  => "This name is already added",
                       ]);


            if (!empty($request->stock_in)) {

                $new_stock = $inventory->stock_in + $request->stock_in;

            }

            if (!empty($request->stock_out)) {
                $new_stock = $inventory->stock_in - $request->stock_out;

            }

           
            $inventory_update = DB::table('inventory')
            ->where('category_id', $request->category_id)
            ->where('material_name', $request->material_name)
            ->update(["stock_in" => $new_stock]);


            if ($inventory->stock_in < $request->stock_out) {

                return response()->json([
                    "status" => true,
                    "message" => "stock not available",
                    "available stock" => $inventory->stock_in
                ]);
            }

            if ($inventory_update) {

                $inventory_data1 = DB::table('inventory')->where('material_name', $request->material_name)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Inventory details updated successfully",
                    "data" => $inventory_data1
                ]);

            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Inventory details updated failed",
                    "data" => []
                ]);
            }


        } else {

            $inventory = DB::table('inventory')->insert($data);

            if ($inventory) {
                $inventory_data = DB::table('inventory')->where('material_name', $request->material_name)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Inventory details added successfully",
                    "data" => $inventory_data
                ]);

            } else {

                return response()->json([
                    "status" => false,
                    "message" => "Inventory category details added failed",
                    "data" => []
                ]);
            }

        }
    }
    // public function store_inventory(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [

    //         'category_id' => 'required',
    //         'material_name' => 'required',

    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json($validator->errors());
    //     }
    //     try {

    //         $data = [
    //             "category_id" => $request->category_id,
    //             "material_name" => $request->material_name,
    //             "make" => $request->make,
    //             "stock_in" => $request->stock_in,
    //             "price" => $request->price,
    //             "status" => $request->status,
    //             "created_at" => date("Y-m-d H:i:s"),
    //             "updated_at" => date("Y-m-d H:i:s")
    //         ];


    //         $inventory = DB::table('inventory')->insert($data);

    //         if ($inventory) {
    //             $inventory_data = DB::table('inventory')->where('category_id', $request->category_id)->first();

    //             return response()->json([
    //                 "status" => true,
    //                 "message" => "Inventory details added successfully",
    //                 "data" => $inventory_data
    //             ]);
    //         } else {

    //             return response()->json([
    //                 "status" => false,
    //                 "message" => "Inventory category details added failed",
    //                 "data" => []
    //             ]);
    //         }
    //     } catch (\Exception $e) {

    //         \Log::error('Exception occurred: ' . $e->getMessage());
    //         return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.']);
    //     }
    // }



    public function inventory_list()
    {

        $inventory_list = DB::table('inventory')
            ->select('inventory_categories.name as category_name', 'inventory.id', 'inventory.category_id', 'inventory.material_name', 'inventory.make', 'inventory.stock_in','inventory.stock_out', 'inventory.price', 'inventory.status', 'inventory.condition_status', 'inventory.created_at', 'inventory.updated_at')
            ->join('inventory_categories', 'inventory_categories.id', '=', 'inventory.category_id')
            ->orderBy('inventory.id', 'DESC')
            ->get();

        if (!$inventory_list->isEmpty()) {

            return response()->json([
                "status" => true,
                "message" => "Inventory list success",
                "data" => $inventory_list
            ]);
        } else {

            return response()->json([
                "status" => false,
                "message" => "Inventory list not available",
                "data" => []
            ]);
        }
    }


    public function update_inventory(Request $request)
    {


        $data = [
            "category_id" => $request->category_id,
            "material_name" => $request->material_name,
            "make" => $request->make,
            "stock_in" => $request->stock_in,
            "stock_out" => $request->stock_out,
            "price" => $request->price,
            "status" => $request->status,
            "condition_status" => $request->condition_status,
            "created_at" => date("Y-m-d H:i:s"),
            "updated_at" => date("Y-m-d H:i:s")
        ];


        $inventory = DB::table('inventory')->where('id', $request->id)->update($data);

        if ($inventory) {
            $inventory_data = DB::table('inventory')->where('id', $request->id)->first();

            return response()->json([
                "status" => true,
                "message" => "Inventory details updated successfully",
                "data" => $inventory_data
            ]);
        } else {

            return response()->json([
                "status" => false,
                "message" => "Inventory category details updated failed",
                "data" => []
            ]);
        }
    }

    public function delete_inventory(Request $request)
    {

        $inventry = DB::table('inventory')->where('id', $request->id)->delete();

        if ($inventry) {

            return response()->json([
                "status" => true,
                "message" => "Inventory details deleted success",

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Inventory details delete failed",
                "data" => []
            ]);
        }
    }


    public function inventory_details(Request $request)
    {


        $inventory_data = DB::table('inventory')->where('category_id', $request->category_id)->get();


        if (!$inventory_data->isEmpty()) {

            return response()->json([
                "status" => true,
                "message" => "Inventory details success",
                "data" => $inventory_data

            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Inventory details failed",
                "data" => []

            ]);
        }
    }
}