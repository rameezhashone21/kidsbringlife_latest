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
        
        $attendance_check=Participant_attendance::where('participant_id',$id)
                            ->where('date',$request->date)
                            ->get();
        
        if (count($attendance_check)>0) {

            Participant_attendance::where('participant_id', $id)
                                    ->where('date',$request->date)
                                    ->update(['attendance' => $request->attendance]);
            
            return response(['status' => '1', 'message' => 'Attendance Updated'], 201);
        }
        else{
            
            $attendance=Participant_attendance::create([
            'participant_id'    => $id,
            'date'    => $request->date,
            'attendance'     => $request->attendance
            ]);
        
            return response(["result" => $attendance, 'status' => '1', 'message' => 'Attendance Marked'], 201);

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
        
        $meal_check=Participant_meal::where('participant_id',$id)
                            ->where('date',$request->date)
                            ->where('meal_type',$request->meal_type)
                            ->get();
                            
        if (count($meal_check)>0) {

            Participant_meal::where('participant_id', $id)
                                    ->where('date',$request->date)
                                    ->where('meal_type',$request->meal_type)
                                    ->update(['meal_taken' => $request->meal_taken]);
            
            return response(['status' => '1', 'message' => 'Meal Updated'], 201);
        }
        else{
            
            $meal=Participant_meal::create([
            'participant_id'    => $id,
            'meal_type'       => $request->meal_type,
            'date'    => $request->date,
            'meal_taken'     => $request->meal_taken
            ]);
        
            return response(["result" => $meal, 'status' => '1', 'message' => 'Meal Marked'], 201);

        }                    

    }
}
