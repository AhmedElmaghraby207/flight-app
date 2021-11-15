<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripsController extends BaseApiController
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Store new trip function
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_city' => 'required|string|min:3|max:35',
            'destination_city' => 'required|string|min:3|max:35',
            'price' => 'required|numeric',
            'take_off_time' => 'required|date_format:Y-m-d H:i:s',
            'landing_time' => 'required|date_format:Y-m-d H:i:s',
        ]);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()]);

        $trip = new Trip();
        $trip->origin_city = $request->input('origin_city');
        $trip->destination_city = $request->input('destination_city');
        $trip->price = $request->input('price');
        $trip->take_off_time = $request->input('take_off_time');
        $trip->landing_time = $request->input('landing_time');
        $saved_trip = $trip->save();

        if ($saved_trip)
            return response()->json(['status_code' => 200, 'message' => 'Trip saved successfully!']);
        else
            return response()->json(['status_code' => 400, 'message' => 'Something went wrong!']);
    }
}
