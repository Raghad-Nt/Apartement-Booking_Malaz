<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentImageResource extends JsonResource
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
            'apartment_id' => $this->apartment_id,
            'image_url' => asset('storage/' . $this->image_path),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}