<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Trip;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
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

    /**
     * Search for available trips
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_city' => 'required|string|min:3|max:35',
            'destination_city' => 'required|string|min:3|max:35',
            'type' => 'required|numeric|in:0,1',
        ]);
        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()]);

        $trips = Trip::all(); //Not the best solution because it returns all DB items and filter them
        $found_trips = [];
        $total_price = 0;
        $duration = 0;
        if ($request->input('type') == 0) { // Cheapest
            $cheapest_direct_trip = $trips
                ->where('origin_city', $request->input('origin_city')) //Our search origin_city
                ->where('destination_city', $request->input('destination_city')) //Our search destination_city
                ->sortBy("price")
                ->first();
            $found_trips[] = $cheapest_direct_trip;
            $total_price = $cheapest_direct_trip->price;

            $take_off_time = new \DateTime($cheapest_direct_trip->take_off_time);
            $landing_time = new \DateTime($cheapest_direct_trip->landing_time);
            $duration = $landing_time->diff($take_off_time)->format('%H hours %i minutes');

            //get first transits for indirect trip [Our origin_city to transit city] ex: Cairo => Dubai
            $first_transits = $trips
                ->where('origin_city', $request->input('origin_city')) //Our search origin_city
                ->where('destination_city', '!=', $request->input('destination_city')); //Other destination_city [transit]

            if (count($first_transits) > 0) {
                foreach ($first_transits as $first_transit) {
                    //get indirect trips [from first transit destination as the origin to destination city] ex: Dubai => Tokyo
                    $last_transit = $trips
                        ->where('origin_city', $first_transit->destination_city) //origin city is the destination city for the first transit
                        ->where('destination_city', $request->input('destination_city')) //the destination city is our search destination_city
                        ->sortBy("price")
                        ->first();
                    if ($last_transit->count() > 0) {
                        $total_price_indirect = $first_transit->price + $last_transit->price;

                        if ($total_price_indirect < $total_price) {
                            $total_price = $total_price_indirect;
                            $found_trips = []; //empty found trips array
                            $found_trips[] = $first_transit;
                            $found_trips[] = $last_transit;

                            $take_off_time = new \DateTime($first_transit->take_off_time);
                            $landing_time = new \DateTime($last_transit->landing_time);
                            $duration = $landing_time->diff($take_off_time)->format('%H hours %i minutes');
                        }
                    }
                }
            }

        } elseif ($request->input('type') == 1) {
            $fastest_direct_trip = $trips
                ->where('origin_city', $request->input('origin_city')) //Our search origin_city
                ->where('destination_city', $request->input('destination_city')) //Our search destination_city
                ->sortBy("landing_time")
                ->first();
            $found_trips[] = $fastest_direct_trip;
            $total_price = $fastest_direct_trip->price;
            $take_off_time = new \DateTime($fastest_direct_trip->take_off_time);
            $landing_time = new \DateTime($fastest_direct_trip->landing_time);
            $duration = $landing_time->diff($take_off_time)->format('%H hours %i minutes');

            //get first transits for indirect trip [Our origin_city to transit city] ex: Cairo => Dubai
            $first_transits = $trips
                ->where('origin_city', $request->input('origin_city')) //Our search origin_city
                ->where('destination_city', '!=', $request->input('destination_city')); //Other destination_city [transit]

            if (count($first_transits) > 0) {
                foreach ($first_transits as $first_transit) {
                    //get indirect trips [from first transit destination as the origin to destination city] ex: Dubai => Tokyo
                    $last_transit = $trips
                        ->where('origin_city', $first_transit->destination_city) //origin city is the destination city for the first transit
                        ->where('destination_city', $request->input('destination_city')) //the destination city is our search destination_city
                        ->sortBy("landing_time")
                        ->first();
                    if ($last_transit->count() > 0) {
                        $total_price_indirect = $first_transit->price + $last_transit->price;
                        if ($last_transit->landing_time < $first_transit->landing_time) {
                            $found_trips = []; //empty found trips array
                            $found_trips[] = $first_transit;
                            $found_trips[] = $last_transit;
                            $total_price = $total_price_indirect < $total_price ? $total_price_indirect : $total_price;

                            $take_off_time = new \DateTime($first_transit->take_off_time);
                            $landing_time = new \DateTime($last_transit->landing_time);
                            $duration = $landing_time->diff($take_off_time)->format('%H hours %i minutes');
                        }
                    }
                }
            }
        }

        return response()->json([
            'results' =>
                [
                    'total price' => $total_price,
                    'duration' => $duration,
                    'schedule' => $found_trips
                ]
        ]);
    }
}
