<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Event;
use App\Models\Event_user;
use App\Models\Event_meal;
use DateTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Meal;
use DB;
use stdClass;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::all();
        if (count($events) > 0) {
            return response(["Data" => $events, 'statusCode' => '200', 'message' => 'All Events'], 201);
        } else {
            return response(['statusCode' => '404', 'message' => 'No Data Found'], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_name' => 'required|max:255',
            'details' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'meal_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 422);
        }
       
        $event = Event::create([
            'event_name'    => $request->event_name,
            'details'       => $request->details,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'meal_type'     => $request->meal_type
        ]);

        if($request->meal_type == 1) $event->meals()->attach($request->meal_type, ['time' => $request->snack_time]);
        else if($request->get('meal_type')== 2) $event->meals()->attach($request->meal_type, ['time' => $request->supper_time]);
        else if($request->get('meal_type')== 3) {
            $event->meals()->attach(1, ['time' => $request->snack_time]);
            $event->meals()->attach(2, ['time' => $request->supper_time]);
        }

        foreach ($request->user_id as $user_id) {
            $event_user = new Event_user();
            $event_user->event_id = $event->id;
            $event_user->user_id = $user_id;
            $event_user->save();

            User::where('id',$user_id)->update(["assigned_to_event" => 1]);
        }

        if (isset($event)) {
            return response(["Data" => $event, 'statusCode' => '200', 'message' => 'Event Created Successfully'], 201);
        } else {
            return response(['statusCode' => '404', 'message' => 'Event failed to create'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::where('id', $id)->get();
        if (count($event) > 0) {
            Event_user::where('event_id', $id)->delete();
            Event::where('id', $id)->delete();
            return response(["Data" => $event, 'statusCode' => '200', 'message' => 'Event Deleted Successfully'], 201);
        } else {
            return response(["Data" => $event, 'statusCode' => '404', 'message' => 'Event failed to Delete'], 404);
        }
    }

    /**
     * Get the specific resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function details($id)
    {
        // $users = Event::where('id', $id)
        //     ->with('users:id,name')
        //     ->first();

        $meals = Event::where('id', $id)
            ->with('users:id,name')
            ->with('meals:id,title')
            ->with('event_meals')
            ->first();

            //dd($meals);

        if ($meals) {
            return response(["Data" => $meals, 'statusCode' => '200', 'message' => 'Event Details'], 201);
        } else {
            return response(['statusCode' => '404', 'message' => 'No Data Found'], 404);
        }
    }

    /**
     * Get the specific resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function my_event()
    {
        $id = Auth::id();

        $event_id = Event_user::where('user_id', $id)->value('event_id');
        $event = Event::where('id', $event_id)
            ->with('meals:id,title')
            ->with('event_meals')
            ->first();


        if ($event > 0) {
            return response(["result" => $event, 'status' => '200', 'message' => 'My Event'], 200);
        } else {
            return response(['status' => '0', 'message' => 'No Data Found'], 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function event_close($id)
    {
        
        $event = Event::find($id);
        $event->status = "2";
        $event->save(); 
        
        Event_user::where('event_id', $id)
            ->join('users', 'event_users.user_id', '=', 'users.id')
            ->update(['assigned_to_event' => '2']);

        
        if (isset($event)) {
            return response(["result" => $event, 'status' => '200', 'message' => 'Event Closed'], 200);
        } else {
            return response(['status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'event_name' => 'required|max:255',
            'details' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'meal_type' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $event_edit = Event::find($id);
        $event = Event::where('id', $id)->get();

        if (count($event) > 0) {
            $event_edit->event_name =  $request->get('event_name');
            $event_edit->details = $request->get('details');
            $event_edit->start_date = $request->get('start_date');
            $event_edit->end_date = $request->get('end_date');
            $event_edit->meal_type = $request->get('meal_type');
            $event_edit->save();

            Event_user::where('event_id', $id)->delete();

            foreach ($request->user_id as $user_id) {
                $event_user = new Event_user();
                $event_user->event_id = $id;
                $event_user->user_id = $user_id;
                $event_user->save();

                User::where('id',$user_id)->update(["assigned_to_event" => 1]);
            }

            return response(["Data" => $event_edit, 'statusCode' => '200', 'message' => 'Event Updated Successfully'], 201);
        } else {
            return response(['statusCode' => '404', 'message' => 'Event failed to update'], 404);
        }
    }

    public function get_users()
    {
        $users= User::whereIn('assigned_to_event', [0, 2])->get();

        // whereHas('asso_events', function($q) {
        //     $q->doesntExist();
        // })
        
        if (count($users) > 0) {
            return response(["result" => $users, 'status' => '1', 'message' => 'Unasigned users'], 200);
        } else {
            return response(['status' => '0', 'message' => 'No Data Found'], 200);
        }
    }

    public function get_meal()
    {
        $meals = Meal::all();
        if (count($meals) > 0) {
            return response(["Data" => $meals, 'statusCode' => '200', 'message' => 'Meal Type'], 201);
        } else {
            return response(['statusCode' => '404', 'message' => 'No Data Found'], 404);
        }
    }
}
