<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'event_name' => 'required|max:255',
            'details' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'meal_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 422);
        }

        $record = Activity::create($request->except('token'));
        $record->asso_event()->attach($request->event_id);

        if(!$record)  return response()->json([

        ], 501);

        return response()->json([

        ], 201);
    }
}
