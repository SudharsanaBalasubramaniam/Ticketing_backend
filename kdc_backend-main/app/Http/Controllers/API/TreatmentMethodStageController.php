<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TreatmentMethodStageController extends Controller
{
    // Get all treatment method stages
    public function index()
    {
        $data = DB::table('treatment_method_stages as ts')
            ->join('treatment_plan_method as tm', 'ts.treatment_method_id', '=', 'tm.id')
            ->select(
                'ts.id',
                'ts.treatment_method_id',
                'tm.treatment_method',
                'ts.stage',
                'ts.created_at',
                'ts.updated_at'
            )
            ->where('ts.active', 1)
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No active treatment method stages found',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => true,
            "message" => "Treatment method stage retrieved successfully",
            'data' => $data
        ]);
    }


    // Store new treatment method stage
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'treatment_method_id' => 'required|integer',
            'stage' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Check if stage already exists for that treatment_method_id
        $exists = DB::table('treatment_method_stages')
            ->where('treatment_method_id', $request->treatment_method_id)
            ->where('stage', $request->stage)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Stage already exists for this treatment method'
            ], 409);
        }

        // DB::table('treatment_method_stages')->insert([
        //     'treatment_method_id' => $request->treatment_method_id,
        //     'stage' => $request->stage,
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);

        $id = DB::table('treatment_method_stages')->insertGetId([
            'treatment_method_id' => $request->treatment_method_id,
            'stage' => $request->stage,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $newlyInserted = DB::table('treatment_method_stages')->where('id', $id)->first();

        return response()->json(['status' => true, 'message' => 'Stage added successfully',  "data" => $newlyInserted]);
    }

    // Update existing stage
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'treatment_method_id' => 'required|integer',
            'stage' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Check for uniqueness
        $duplicate = DB::table('treatment_method_stages')
            ->where('treatment_method_id', $request->treatment_method_id)
            ->where('stage', $request->stage)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'status' => false,
                'message' => 'Another stage with the same name already exists for this treatment method'
            ], 409);
        }

        DB::table('treatment_method_stages')
            ->where('id', $request->id)
            ->update([
                'treatment_method_id' => $request->treatment_method_id,
                'stage' => $request->stage,
                'updated_at' => now()
            ]);

        $updatedData = DB::table('treatment_method_stages as ts')
            ->join('treatment_plan_method as tm', 'ts.treatment_method_id', '=', 'tm.id')
            ->select(
                'ts.id',
                'ts.treatment_method_id',
                'tm.treatment_method',
                'ts.stage',
                'ts.created_at',
                'ts.updated_at'
            )
            ->where('ts.id', $request->id)
            ->first();

        return response()->json(['status' => true, 'message' => 'Stage updated successfully', "data" => $updatedData]);
    }

    // Delete a stage
    public function destroy(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to update the row’s active flag
        $affected = DB::table('treatment_method_stages')
            ->where('id', $request->id)
            ->update([
                'active'     => 0,            // mark as inactive
                'updated_at' => now()         // keep timestamp accurate
            ]);

        if ($affected === 0) {
            // No matching row found or already inactive
            return response()->json([
                'status'  => false,
                'message' => 'Stage not found or already inactive'
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Stage deactivated successfully'
        ]);
    }

    public function getByMethodId(Request $request)
    {

        $ids = $request->input('method_id');

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or missing ID list.'
            ]);
        }

        $stages = DB::table('treatment_method_stages')
            ->where('treatment_method_id', $ids)
            ->get();

        if ($stages->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No treatment stage not found'
            ]);
        }

        return response()->json([
            'status'  => true,
            'data' => $stages,
            'message' => 'Treatment method stage retrieved successfully'
        ]);
    }
}
