<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DiscountType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DiscountTypeController extends Controller
{
    public function getDiscountType()
    {
        try {
            $getDiscountType = DiscountType::get();
            if (!$getDiscountType->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Retrieved Discount Type details.",
                    "data" => $getDiscountType
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No data available.",
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function createDiscountType(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'discount_type' => 'required|in:flat,percentage',
            'value' => 'required|numeric',
            'name' => 'required|string|max:200|min:1',
            'is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        try {
            $data = $request->all();
            $existDiscountType = DiscountType::where('discount_type', $data['discount_type'])
                ->where('name', $data['name'])
                ->first();

            if (!empty($existDiscountType)) {
                return response()->json([
                    "status" => true,
                    "message" => "Discount Type name already exists for the discount type.",
                ], 409);
            }

            $saveDiscountType = DiscountType::create($data);
            if ($saveDiscountType) {
                return response()->json([
                    "status" => true,
                    "message" => "Discount Type insert successfully.",
                    "data" => $saveDiscountType
                ], 201);
            }
        } catch (\Exception $e) {

            Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function updateDiscountType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:discount_type,id',
            'discount_type' => 'required|in:flat,percentage',
            'value' => 'required|numeric',
            'name' => 'required|string|max:200|min:1',
            'is_active' => 'nullable|boolean',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $data = $request->all();
            $existDiscountType = DiscountType::where('discount_type', $data['discount_type'])
                ->where('name', $data['name'])
                ->where('id', '!=', $data['id'])
                ->first();
            if (!empty($existDiscountType)) {
                return response()->json([
                    "status" => true,
                    "message" => "Discount Type already exists.",
                ], 409);
            }

            $updateDiscountType = DiscountType::where('id', $request->id)->update($data);

            if ($updateDiscountType) {
                $selectDiscountType = DiscountType::where('id', $request->id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Discount Type updated successfully.",
                    "data" => $selectDiscountType
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Discount Type updated failed",
                ]);
            }
        } catch (\Exception $e) {

            Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' =>  'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
    public function updateDiscountTypeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:discount_type,id',
            'is_active' => 'nullable|boolean',

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $updateDiscountType = DiscountType::where('id', $request->id)->update([
                'is_active' => $request->is_active
            ]);
            if ($updateDiscountType) {
                $selectDiscountType = DiscountType::where('id', $request->id)->first();
                return response()->json([
                    "status" => true,
                    "message" => "Discount Type updated successfully.",
                    "data" => $selectDiscountType
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Discount Type updated failed",
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' =>  'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }


    public function deleteDiscountType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:discount_type,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $deleteDiscountType = DiscountType::where('id', $request->id)->delete();

            if ($deleteDiscountType) {

                return response()->json([
                    "status" => true,
                    "message" => "Discount Type deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Discount Type delete failed.",
                ]);
            }
        } catch (\Exception $e) {

            Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Something went wrong.', 'error' => $e->getMessage()]);
        }
    }
}
