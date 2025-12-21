<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'price' => '$' . number_format($this->price, 2),
            'province' => $this->province,
            'city' => $this->city,
            'features' => $this->features,
            'owner_id' => $this->owner_id,
            'status' => $this->status,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'images' => ApartmentImageResource::collection($this->whenLoaded('images')),
           // 'created_at' => $this->created_at,
           // 'updated_at' => $this->updated_at
        ];
    }
}