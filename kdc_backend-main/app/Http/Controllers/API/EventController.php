<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use URL;

class EventController extends Controller
{


    public function getEvent()
    {
        try {
            $getEvent = DB::table('events')->get();
            if (!$getEvent->isEmpty()) {
                return response()->json([
                    "status" => true,
                    "message" => "Fetch events details.",
                    "data" => $getEvent
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No data available.",
                    "data" => []

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function createEvent(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required',
            'date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        // Now you can use $imageUrl or any other data as needed
        $title = $request->input('title');
        $date = $request->input('date');

        $documentName_path = ""; // Initialize the $image variable

        if ($request->hasFile('image')) {
            // Handle file upload
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('events'), $imageName);
            $documentName_path = URL::to('/public/events/' .  $imageName);
        } else {
            if (Str::contains($request->image, 'https')) {
                $documentName_path = $request->image;
            } else {
                $documentName_path = "";
            }
        }

        try {
            $data = [
                "title" => $title,
                "image" => $documentName_path,
                "date" => $date,
                "created_at" => now(),
                "updated_at" => now()
            ];

            $saveEvent = DB::table('events')->insert($data);
            if ($saveEvent) {
                $Event = DB::table('events')->where('title', $request->title)->orderBy('id', 'DESC')->first();
                return response()->json([
                    "status" => true,
                    "message" => "Event insert successfully.",
                    "data" => $Event
                ], 201);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }

    public function updateEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            // Now you can use $imageUrl or any other data as needed
            $title = $request->input('title');
            $date = $request->input('date');

            $bill_copy_path = "";

            if ($request->hasFile('image')) {
                // Handle file upload
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('events'), $imageName);
                $bill_copy_path = URL::to('/public/events/' .  $imageName);
            } else {
                if (Str::contains($request->image, 'https' || 'http')) {
                    $bill_copy_path = $request->image;
                } else {
                    $bill_copy_path = "";
                }
            }

            // if (!empty($request->image)) {
            //     if (Str::contains($request->image, 'https' || 'http')) {
            //         $bill_copy_path = $request->image;
            //     } else {
            //         $document_image = time() . '.' . $request->image->extension();
            //         $nes = $request->image->move(public_path('events'), $document_image);
            //         $bill_copy_path = URL::to('/public/events/' .  $document_image);
            //     }
            // }

            $data = [
                "title" => $title,
                "image" => $bill_copy_path,
                "date" => $date,
                "updated_at" => now()
            ];

            $updateEvent = DB::table('events')->where('id', $request->id)->update($data);

            if ($updateEvent) {
                $selectEvent = DB::table('events')->where('id', $request->id)->first();

                return response()->json([
                    "status" => true,
                    "message" => "Event updated successfully.",
                    "data" => $selectEvent
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Event updated failed",
                    "data" => []
                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' =>  'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }


    public function deleteEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        try {
            $deleteEvent = DB::table('events')->where('id', $request->id)->delete();

            if ($deleteEvent) {

                return response()->json([
                    "status" => true,
                    "message" => "Event deleted successfully.",

                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Event delete failed.",
                    "data" => []

                ]);
            }
        } catch (\Exception $e) {

            \Log::error('Exception occurred: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Somthing went wrong.', 'error' => $e->getMessage()]);
        }
    }
}
