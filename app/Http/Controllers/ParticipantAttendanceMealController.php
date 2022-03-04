<?php

namespace App\Http\Controllers;

use App\Models\Participant_attendance;
use App\Models\Participant_meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class ParticipantAttendanceMealController extends Controller
{
    public function mark_attendance($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
            'attendance' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 422);
        }

        $attendance=Participant_attendance::create([
            'participant_id'    => $id,
            'date'    => $request->date,
            'attendance'     => $request->attendance
        ]);

        if ($attendance) {
            return response(["result" => $attendance, 'status' => '1', 'message' => 'Attendance Marked'], 201);
        } else {
            return response(['status' => '0', 'message' => 'No Data Found'], 200);
        }

    }

    public function mark_meal($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meal_type' => 'required',
            'date' => 'required',
            'meal_taken' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 422);
        }

       $meals=Participant_meal::create([
            'participant_id'    => $id,
            'meal_type'       => $request->meal_type,
            'date'    => $request->date,
            'meal_taken'     => $request->meal_taken
        ]);

        if ($meals) {
            return response(["result" => $meals, 'status' => '1', 'message' => 'Meal Marked'], 201);
        } else {
            return response(['status' => '0', 'message' => 'No Data Found'], 200);
        }

    }
}
