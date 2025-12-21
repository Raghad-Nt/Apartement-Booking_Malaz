<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'apartment_id' => $this->apartment_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'total_price' => '$' . number_format($this->total_price, 2),
            'user' => new UserResource($this->whenLoaded('user')),
            'apartment' => new ApartmentResource($this->whenLoaded('apartment')),
            'created_at' => $this->created_at,
           // 'updated_at' => $this->updated_at
        ];
    }
}