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
use App\Models\Event_day;
use DateTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Meal;
use App\Models\Participant;
use App\Models\Participant_meal;
use DB;
use stdClass;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $events = Event::
              orwhere('event_name','LIKE','%'.$request->q.'%')
            ->with('users:id,name,email')
            ->with('locations:id,location_name')
            ->orderBy('id', 'DESC')
            ->paginate(10);
            
        if (count($events) > 0) {
            return response(["Data" => $events, 'statusCode' => '1', 'message' => 'Events'], 200);
        } else {
            return response(["Data" => array(), 'statusCode' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function get_all_events(Request $request)
    {
        $events = Event::
              orwhere('event_name','LIKE','%'.$request->q.'%')
            ->with('users:id,name,email')
            ->with('locations:id,location_name')
            ->orderBy('id', 'DESC')
            ->get();
            
        if (count($events) > 0) {
            return response(["Data" => $events, 'statusCode' => '1', 'message' => 'Events'], 200);
        } else {
            return response(["Data" => array(), 'statusCode' => '0', 'message' => 'No Data Found'], 200);
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
            'location_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'Validation Error'], 422);
        }
       
        $event = Event::create([
            'event_name'    => $request->event_name,
            'details'       => $request->details,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'meal_type'     => $request->meal_type,
            'location_id'     => $request->location_id,
        ]);

        if($request->meal_type == 1) $event->meals()->attach($request->meal_type, ['time' => $request->snack_time]);
        else if($request->get('meal_type')== 2) $event->meals()->attach($request->meal_type, ['time' => $request->supper_time]);
        else if($request->get('meal_type')== 3) {
            $event->meals()->attach(1, ['time' => $request->snack_time]);
            $event->meals()->attach(2, ['time' => $request->supper_time]);
        }

       
        foreach ($request->user_id as $user_id) {
            $event_user = new Event_user();         
            
            Event_user::where('user_id', $user_id)->delete();
            
            $event_user->event_id = $event->id;
            $event_user->user_id = $user_id;
            $event_user->save();

            User::where('id',$user_id)->update(["assigned_to_event" => 1]);
        }
        
         foreach ($request->days as $days) {
            $event_day = new Event_day();
            $event_day->event_id = $event->id;
            $event_day->days = $days;
            $event_day->save();

        }

        // //finding all dates of event
        // $period = CarbonPeriod::create($request->start_date, $request->end_date);

        // //Inserting date wise data for attendance marking
        // foreach ($period as $date) {
        //     $date_event=$date->format('Y-m-d');

        //     if($request->get('meal_type')== 1){
        //         $event->meal_participant()->attach($request->meal_type, ['date' => $date_event]);
        //     }
        //     if($request->get('meal_type')== 2){
        //         $event->meal_participant()->attach($request->meal_type, ['date' => $date_event]);
        //     }
        //     if($request->get('meal_type')== 3){
        //         $event->meal_participant()->attach(1, ['date' => $date_event]);
        //         $event->meal_participant()->attach(2, ['date' => $date_event]);
        //     }
            
        // }

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
        
        Event_user::where('event_id', $id)
            ->join('users', 'event_users.user_id', '=', 'users.id')
            ->update(['assigned_to_event' => '2']);

        $event = Event::where('id', $id)->get();
        if (count($event) > 0) {
            Event_user::where('event_id', $id)->delete();
            Event_meal::where('event_id', $id)->delete();
            Event_day::where('event_id', $id)->delete();
            Event::where('id', $id)->delete();
            return response(["Data" => $event, 'statusCode' => '1', 'message' => 'Event Deleted Successfully'], 200);
        } else {
            return response(["Data" => array(), 'statusCode' => '0', 'message' => 'Event failed to Delete'], 200);
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
            ->with('event_days')
            ->with('locations:id,location_name')
            ->first();

            //dd($meals);

        if ($meals) {
            return response(["Data" => $meals, 'statusCode' => '1', 'message' => 'Event Details'], 200);
        } else {
            return response(["Data" => array(), 'statusCode' => '0', 'message' => 'No Data Found'], 200);
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

        $event_id = Event_user::where('user_id', $id)
                    ->where('status',1)
                    ->value('event_id');

        $event = Event::where('id', $event_id)
            ->with('meals:id,title')
            ->with('event_meals')
            ->with('event_days')
            ->with('locations:id,location_name,address')
            ->first();
            
        if ($event) {
            return response(["result" => $event, 'status' => '1', 'message' => 'My Event'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }

    public function event_participants(Request $request)
    {
        $full_name=$request->q;
        
        //dd($request->all());
        $id = Auth::id();
        $event_id = Event_user::where('user_id', $id)
                    ->where('status',1)
                    ->value('event_id');

        $participant_id = Participant::select('id')->where('event_id', $event_id)->get();    
       
        $results = Participant::with('participant_attendance')
                        ->with('participant_meals')
                        ->whereIn('id', $participant_id)
                        ->where('full_name','like','%'.$full_name.'%' )
                        ->orderBy('id', 'DESC')
                        ->paginate(10); 
                        
       
        if ($results) {
            return response(["result" => $results, 'status' => '1', 'message' => 'My Event'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function event_participants_withoutpaginated(Request $request)
    {
        $full_name=$request->q;
        
        //dd($request->all());
        $id = Auth::id();
        $event_id = Event_user::where('user_id', $id)
                    ->where('status',1)
                    ->value('event_id');

        $participant_id = Participant::select('id')->where('event_id', $event_id)->get();    
       
        $results = Participant::with('participant_attendance')
                        ->with('participant_meals')
                        ->whereIn('id', $participant_id)
                        ->where('full_name','like','%'.$full_name.'%' )
                        ->orderBy('id', 'DESC')
                        ->get(); 
                        
       
        if ($results) {
            return response(["result" => $results, 'status' => '1', 'message' => 'My Event'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function admin_event_participants($event_id)
    {

        $participant_id = Participant::select('id')->where('event_id', $event_id)->get();    
       
        $results = Participant::with('participant_attendance')
                        ->with('participant_meals')
                        ->whereIn('id', $participant_id)
                        ->orderBy('id', 'DESC')
                        ->paginate(10); 
                        
       
        if ($results) {
            return response(["result" => $results, 'status' => '1', 'message' => 'Event Participants'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function admin_event_participants_without_pagination($event_id)
    {

        $participant_id = Participant::select('id')->where('event_id', $event_id)->get();    
       
        $results = Participant::with('participant_attendance')
                        ->with('participant_meals')
                        ->whereIn('id', $participant_id)
                        ->orderBy('id', 'DESC')
                        ->get(); 
                        
       
        if ($results) {
            return response(["result" => $results, 'status' => '1', 'message' => 'Event Participants'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function specific_event_report($event_id)
    {

        $participant_id = Participant::select('id')->where('event_id', $event_id)->get(); 
        
        $no_of_particpants=count($participant_id);
        
        $meal_type = Event::where('id', $event_id)->value('meal_type');
        $event_start_date = Event::where('id', $event_id)->value('start_date');
        $event_end_date = Event::where('id', $event_id)->value('end_date');
        $event_days = Event_Day::select('days')->where('event_id', $event_id)->get();
    
        
        $snack_given = Participant_meal::select('participant_id','date')->where('meal_type',1)->wherein('participant_id', $participant_id)->get();
        $supper_given = Participant_meal::select('participant_id','date')->where('meal_type',2)->wherein('participant_id', $participant_id)->get();

        
        $object = new stdClass();
        $object->no_of_students = $no_of_particpants;
        $object->start_date = $event_start_date;
        $object->end_date = $event_end_date;
        $object->days = $event_days;
        
        //dd($meal_type);
        if($meal_type == 1)
        {
            $object->total_snacks = array("Snacks"=>$no_of_particpants);
            $object->snack_given = $snack_given;
        }
        elseif($meal_type == 2)
        {
            $object->total_supper = array("Suppers"=>$no_of_particpants);
            $object->supper_given = $no_of_particpants;
        }
        elseif($meal_type == 3)
        {
            $object->total_snacks = array("Snacks"=>$no_of_particpants);
            $object->total_supper = array("Suppers"=>$no_of_particpants);
            $object->snack_given = $snack_given;
            $object->supper_given = $supper_given;
        }

 
   
        if ($object) {
            return response(["result" => $object, 'status' => '1', 'message' => 'My Event Meal Report'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function meal_report()
    {

        $id = Auth::id();
        $event_id = Event_user::where('user_id', $id)
                    ->where('status',1)
                    ->value('event_id');

        $participant_id = Participant::select('id')->where('event_id', $event_id)->get(); 
        
        $no_of_particpants=count($participant_id);
        
        $meal_type = Event::where('id', $event_id)->value('meal_type');
        $event_start_date = Event::where('id', $event_id)->value('start_date');
        $event_end_date = Event::where('id', $event_id)->value('end_date');
        $event_days = Event_Day::select('days')->where('event_id', $event_id)->get();
    
        
        $snack_given = Participant_meal::select('participant_id','date')->where('meal_type',1)->wherein('participant_id', $participant_id)->get();
        $supper_given = Participant_meal::select('participant_id','date')->where('meal_type',2)->wherein('participant_id', $participant_id)->get();

        
        $object = new stdClass();
        $object->no_of_students = $no_of_particpants;
        $object->start_date = $event_start_date;
        $object->end_date = $event_end_date;
        $object->days = $event_days;
        
        //dd($meal_type);
        if($meal_type == 1)
        {
            $object->total_snacks = array("Snacks"=>$no_of_particpants);
            $object->snack_given = $snack_given;
        }
        elseif($meal_type == 2)
        {
            $object->total_supper = array("Suppers"=>$no_of_particpants);
            $object->supper_given = $supper_given;
        }
        elseif($meal_type == 3)
        {
            $object->total_snacks = array("Snacks"=>$no_of_particpants);
            $object->total_supper = array("Suppers"=>$no_of_particpants);
            $object->snack_given = $snack_given;
            $object->supper_given = $supper_given;
        }

 
   
        if ($object) {
            return response(["result" => $object, 'status' => '1', 'message' => 'My Event Meal Report'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
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

        Event_user::where('event_id',$id)->update(["status" => 2]);

        
        if (isset($event)) {
            return response(["result" => $event, 'status' => '200', 'message' => 'Event Closed'], 200);
        } else {
            return response(['status' => '0', 'message' => 'Event failed to close'], 200);
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
            'location_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }


        $event_edit = Event::find($id);


        $event = Event::where('id', $id)->get();

        
        Event_user::where('event_id', $id)
            ->join('users', 'event_users.user_id', '=', 'users.id')
            ->update(['assigned_to_event' => '2']);

        if (count($event) > 0) {
            $event_edit->event_name =  $request->get('event_name');
            $event_edit->details = $request->get('details');
            $event_edit->start_date = $request->get('start_date');
            $event_edit->end_date = $request->get('end_date');
            $event_edit->meal_type = $request->get('meal_type');
            $event_edit->location_id = $request->get('location_id');
            $event_edit->save();

            Event_user::where('event_id', $id)->delete();
            Event_day::where('event_id', $id)->delete();



            foreach ($request->user_id as $user_id) {
                $event_user = new Event_user();
                
                Event_user::where('user_id', $user_id)->delete();

                $event_user->event_id = $id;
                $event_user->user_id = $user_id;
                $event_user->save();

                User::where('id',$user_id)->update(["assigned_to_event" => 1]);
            }
            
            
            foreach ($request->days as $days) {
                $event_day = new Event_day();
                $event_day->event_id = $id;
                $event_day->days = $days;
                $event_day->save();
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
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    public function get_assigned_users()
    {
        $users= User::where('assigned_to_event', 1)->get();

        // whereHas('asso_events', function($q) {
        //     $q->doesntExist();
        // })
        
        if (count($users) > 0) {
            return response(["result" => $users, 'status' => '1', 'message' => 'Assigned users'], 200);
        } else {
            return response(["result" => array(), 'status' => '0', 'message' => 'No Data Found'], 200);
        }
    }
    
    

    public function get_meal()
    {
        $meals = Meal::all();
        if (count($meals) > 0) {
            return response(["Data" => $meals, 'statusCode' => '1', 'message' => 'Meal Type'], 200);
        } else {
            return response(["Data" => array(), 'statusCode' => '0', 'message' => 'No Data Found'], 200);
        }
    }
}
