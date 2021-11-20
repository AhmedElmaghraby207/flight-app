<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'take-off time' => Carbon::parse($this->take_off_time)->format("m-d-Y H:i"),
            'landing time' => Carbon::parse($this->landing_time)->format("m-d-Y H:i"),
            'origin' => $this->origin_city,
            'destination' => $this->destination_city,
            'price' => (float)$this->price,
        ];
    }
}
