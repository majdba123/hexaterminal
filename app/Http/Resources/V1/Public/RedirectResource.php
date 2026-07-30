<?php

namespace App\Http\Resources\V1\Public;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Redirect
 */
class RedirectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'from_path' => $this->from_path,
            'to_path' => $this->to_path,
            'status_code' => $this->status_code,
        ];
    }
}
