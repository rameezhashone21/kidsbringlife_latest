<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Pra;
use DB;
use stdClass;

class PraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        
        $pra = Pra::create([
            'invoice_number'    => '966213BCLS27325558*test*',
            'POSID'             => '966213',
            'USIN'              => '343232',
        ]);
        
        $data=Pra::value('invoice_number');

        if (isset($data)) {
            return response(["InvoiceNumber" => $data, 'Code' => '100', 'Response' => 'Invoice Recieved Successfully'], 201);
        } else {
            return response(['statusCode' => '404', 'message' => 'Event failed to create'], 404);
        }
    }
}
