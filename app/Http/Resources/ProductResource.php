<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @property mixed $id
 * @property mixed $sku
 * @property mixed $name
 * @property mixed $description
 * @property mixed $price
 * @property mixed $size
 * @property mixed $arm
 * @property mixed $image
 * @property mixed $status
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'size' => $this->size,
            'arm' => $this->arm,
            'image' => Storage::disk('public')->url($this->image),
            'status' => $this->status,
        ];

        return $resource;
    }
}
