<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Location;
use DB;

class LocationController extends Controller
{
    /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
    public function index()
    {
        $locations = Location::all();
        if(count($locations) > 0){
            return response(["Data"=>$locations, 'statusCode' => '200', 'message' => 'All locations'], 201);
        }
        else{
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
        $data = $request->all();

        $validator = Validator::make($data, [
            'location_name' => 'required|max:255',
            'address' => 'required',
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }

        $location = Location::create($data);

        if(isset($location)){
            return response(["Data"=>$location, 'statusCode' => '200', 'message' => 'Location Saved Successfully'], 201);
        }
        else{
            return response(["Data"=>$location, 'statusCode' => '404', 'message' => 'Location failed to save'], 404);
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
        $location = Location::where('id',$id)->get();
        if(count($location) > 0){
            Location::where('id',$id)->delete();
            return response(["Data"=>$location, 'statusCode' => '200', 'message' => 'Location Deleted Successfully'], 201);
        }
        else{
            return response(["Data"=>$location, 'statusCode' => '404', 'message' => 'Location failed to Delete'], 404);
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
        $location = Location::where('id',$id)->get();
        if(count($location) > 0){
            return response(["Data"=>$location, 'statusCode' => '200', 'message' => 'location Details'], 201);
        }
        else{
            return response(['statusCode' => '404', 'message' => 'No Data Found'], 404);
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
            'location_name' => 'required|max:255',
            'address' => 'required',
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['error' => $validator->errors(), 'Validation Error']);
        }
        
        $location_edit = Location::find($id);
        $location = Location::where('id',$id)->get();
        
        if(count($location) > 0){
            $location_edit->location_name =  $request->get('location_name');
            $location_edit->address = $request->get('address');
            $location_edit->lat = $request->get('lat');
            $location_edit->long = $request->get('long');
            $location_edit->save();
            return response(["Data"=>$location_edit, 'statusCode' => '200', 'message' => 'Location Updated Successfully'], 201);
        }
        else{
            return response(['statusCode' => '404', 'message' => 'Location failed to update'], 404);
        }
    }
}
