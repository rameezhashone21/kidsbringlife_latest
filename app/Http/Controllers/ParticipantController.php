<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Participant;
use App\Models\Participant_guardian;
use App\Models\Event_user;
use DB;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id= Auth::id();
        $participants = Participant::where('user_id',$user_id)->get();
        if (count($participants) > 0) {
            return response()->json(["result" => $participants, 'message' => 'All Participants', 'status' => '1'], 200);
        } else {
            return response()->json(['message' => 'No Data Found', 'status' => '0'], 200);
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
        $data = $request->all();

        $validator = Validator::make($data, [
            'first_name' => 'required',
            'last_name' => 'required',
            'age' => 'required',
            'ratial_category' => 'required',
            'address' => 'required',
        ]);

        $user_id= Auth::id();

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $event_id = Event_user::where('user_id', $user_id)->where('status', 1)->value('event_id');

        if(!isset($event_id))
        {
            return response()->json(['message' => 'You cant add any particpant because You are not assigned to any event', 'status' => '0'], 200);
        }

        $participant = new Participant();
        $participant->first_name = $request->first_name;
        $participant->last_name = $request->last_name;
        $participant->allergies = $request->allergies;
        $participant->age = $request->age;
        $participant->ethinic_category = $request->ethinic_category;
        $participant->ratial_category = $request->ratial_category;
        $participant->address = $request->address;
        $participant->guardian = $request->guardian;
        $participant->user_id = $user_id;
        $participant->event_id = $event_id;
        $participant->save();

        if(!isset($participant))
        {
            return response()->json(['message' => 'Particpant failed to create', 'status' => '0'], 200);
        }

        $last_inserted_id = Participant::orderBy('id', 'desc')->value('id');

        if ($request->guardian=="True") {
            
            $participant_guardian = new Participant_guardian();
            $participant_guardian->participant_id = $last_inserted_id;
            $participant_guardian->guardian_firstname = $request->guardian_firstname;
            $participant_guardian->guardian_lastname = $request->guardian_lastname;
            $participant_guardian->guardian_role = $request->guardian_role;
            $participant_guardian->guardian_address = $request->guardian_address;
            $participant_guardian->save();

            return response()->json(["result" => $participant, 'message' => 'Particpant has been created Succesfully with guardian information', 'status' => '1'], 200);

        } else {
            
            return response()->json(['message' => 'Particpant has been created Succesfully without guardian information', 'status' => '1'], 200);

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
        $participants = Participant::where('id', $id)->get();
        if (count($participants) > 0) {
            Participant_guardian::where('participant_id', $id)->delete();
            Participant::where('id', $id)->delete();
            return response(["result" => $participants, 'status' => '1', 'message' => 'Participant Deleted Successfully'], 200);
        } else {
            return response(['status' => '0', 'message' => 'Participant failed to Delete'], 200);
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
        $participant = Participant::where('id', $id)->get();
        if (count($participant) > 0) {
            return response(["Data" => $participant, 'status' => '1', 'message' => 'Participant Details'], 200);
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
            'first_name' => 'required',
            'last_name' => 'required',
            'age' => 'required',
            'ratial_category' => 'required',
            'address' => 'required',
        ]);

        $user_id= Auth::id();

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $event_id = Event_user::where('user_id', $user_id)->where('status', 1)->value('event_id');

        $participant_edit = Participant::find($id);
        $participant = Participant::where('id', $id)->get();

        if (count($participant) > 0) {
            $participant_edit->first_name =  $request->get('first_name');
            $participant_edit->last_name = $request->get('last_name');
            $participant_edit->allergies = $request->get('allergies');
            $participant_edit->age = $request->get('age');
            $participant_edit->ethinic_category = $request->get('ethinic_category');
            $participant_edit->ratial_category = $request->get('ratial_category');
            $participant_edit->address = $request->get('address');
            $participant_edit->guardian = $request->get('guardian');
            $participant->user_id = $user_id;
            $participant->event_id = $event_id;
            $participant_edit->save();

            

            if ($request->guardian=="True") {
            
                Participant_guardian::where('participant_id', $id)->delete();

                $participant_guardian = new Participant_guardian();
                $participant_guardian->participant_id = $id;
                $participant_guardian->guardian_firstname = $request->guardian_firstname;
                $participant_guardian->guardian_lastname = $request->guardian_lastname;
                $participant_guardian->guardian_role = $request->guardian_role;
                $participant_guardian->guardian_address = $request->guardian_address;
                $participant_guardian->save();
    
                return response()->json(["result" => $participant_edit, 'message' => 'Particpant has been updated Succesfully with guardian information', 'status' => '1'], 200);
    
            } else {
                
                return response()->json(['message' => 'Particpant has been updated Succesfully without guardian information', 'status' => '1'], 200);
    
            }

            return response(["result" => $participant_edit, 'status' => '1', 'message' => 'Particpant Updated Successfully'], 200);
        } else {
            return response(['status' => '0', 'message' => 'Event failed to update'], 200);
        }
    }
}
