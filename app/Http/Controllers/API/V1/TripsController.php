<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\TripResource;
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
        $validator = $this->validate_store_method($request);
        if ($validator->fails())
            return response()->json([
                'status' => [
                    'code' => 400,
                    'message' => 'Failed'
                ],
                'errors' => $validator->errors()->first()
            ]);

        $trip = new Trip();
        $trip['origin_city'] = trim(strtolower($request->input('origin_city')));
        $trip['destination_city'] = trim(strtolower($request->input('destination_city')));
        $trip['price'] = (float)$request->input('price');
        $trip['take_off_time'] = trim($request->input('take_off_time'));
        $trip['landing_time'] = trim($request->input('landing_time'));
        $saved_trip = $trip->save();

        if ($saved_trip)
            return response()->json([
                'status' => [
                    'code' => 200,
                    'message' => 'Trip saved successfully!'
                ]
            ]);
        else
            return response()->json([
                'status' => [
                    'code' => 400,
                    'message' => 'Something went wrong!'
                ]
            ]);
    }

    /**
     * Validate store trip method
     *
     * @param $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate_store_method($request) {
        return Validator::make($request->all(), [
            'origin_city' => 'required|string|min:3|max:35',
            'destination_city' => 'required|string|min:3|max:35',
            'price' => 'required|numeric',
            'take_off_time' => 'required|date_format:Y-m-d H:i:s',
            'landing_time' => 'required|date_format:Y-m-d H:i:s|after:take_off_time',
        ]);
    }

    /**
     * Search for available trips
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        $validator = $this->validate_get_method($request);
        if ($validator->fails())
            return response()->json([
                'status' => [
                    'code' => 400,
                    'message' => 'Failed'
                ],
                'errors' => $validator->errors()->first()
            ]);

        $trips = Trip::all(); //Not the best solution because it returns all DB items and filter them
        $found_trips = [];
        $total_price = 0;
        $duration = 0;
        if ($request->input('type') == 0) { // Cheapest
            $cheapest_direct_trip = $trips
                ->where('origin_city', trim(strtolower($request->input('origin_city')))) //Our search origin_city
                ->where('destination_city', trim(strtolower($request->input('destination_city')))) //Our search destination_city
                ->sortBy("price")
                ->first();
            if ($cheapest_direct_trip) {
                $found_trips[] = $cheapest_direct_trip;
                $total_price = $cheapest_direct_trip->price;
                $take_off_time = new \DateTime($cheapest_direct_trip->take_off_time);
                $landing_time = new \DateTime($cheapest_direct_trip->landing_time);
                $duration = $landing_time->diff($take_off_time)->format('%H hours %i minutes');
            }

            //get first transits for indirect trip [Our origin_city to transit city] ex: Cairo => Dubai
            $first_transits = $trips
                ->where('origin_city', trim(strtolower($request->input('origin_city')))) //Our search origin_city
                ->where('destination_city', '!=', trim(strtolower($request->input('destination_city')))); //Other destination_city [transit]

            if (count($first_transits) > 0) {
                foreach ($first_transits as $first_transit) {
                    //get indirect trips [from first transit destination as the origin to destination city] ex: Dubai => Tokyo
                    $last_transit = $trips
                        ->where('origin_city', trim(strtolower($first_transit->destination_city))) //origin city is the destination city for the first transit
                        ->where('destination_city', trim(strtolower($request->input('destination_city')))) //the destination city is our search destination_city
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
                ->where('origin_city', trim(strtolower($request->input('origin_city')))) //Our search origin_city
                ->where('destination_city', trim(strtolower($request->input('destination_city')))) //Our search destination_city
                ->sortBy("landing_time")
                ->first();

            if ($fastest_direct_trip) {
                $found_trips[] = $fastest_direct_trip;
                $total_price = $fastest_direct_trip->price;
                $take_off_time = new \DateTime($fastest_direct_trip->take_off_time);
                $landing_time = new \DateTime($fastest_direct_trip->landing_time);
                $duration = $landing_time->diff($take_off_time)->format('%H hours %i minutes');
            }

            //get first transits for indirect trip [Our origin_city to transit city] ex: Cairo => Dubai
            $first_transits = $trips
                ->where('origin_city', trim(strtolower($request->input('origin_city')))) //Our search origin_city
                ->where('destination_city', '!=', trim(strtolower($request->input('destination_city')))); //Other destination_city [transit]

            if (count($first_transits) > 0) {
                foreach ($first_transits as $first_transit) {
                    //get indirect trips [from first transit destination as the origin to destination city] ex: Dubai => Tokyo
                    $last_transit = $trips
                        ->where('origin_city', trim(strtolower($first_transit->destination_city))) //origin city is the destination city for the first transit
                        ->where('destination_city', trim(strtolower($request->input('destination_city')))) //the destination city is our search destination_city
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

        if (count($found_trips) > 0)
            $found_trips = TripResource::collection($found_trips);
        else
            return response()->json([
                'status' => [
                    'code' => 404,
                    'message' => 'No trips found'
                ],
                'results' => [
                    'total price' => 0,
                    'duration' => 0,
                    'schedule' => []
                ]
            ]);

        return response()->json([
            'status' => [
                'code' => 200,
                'message' => 'Success'
            ],
            'results' => [
                'total price' => (float)$total_price,
                'duration' => $duration,
                'schedule' => $found_trips
            ]
        ]);
    }

    /**
     * Validate get trips method
     *
     * @param $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate_get_method($request) {
        return Validator::make($request->all(), [
            'origin_city' => 'required|string|min:3|max:35',
            'destination_city' => 'required|string|min:3|max:35',
            'type' => 'required|numeric|in:0,1',
        ]);
    }
}
