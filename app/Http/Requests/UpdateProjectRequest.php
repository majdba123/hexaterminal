<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'project_url' => 'nullable|url',
            'description' => 'sometimes|string',
            'video_url' => 'nullable|url',
            'service_id' => 'sometimes|exists:services,id',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'deleted_images' => 'sometimes|array',
            'deleted_images.*' => 'exists:imag__progects,id',
            'features' => 'nullable|array'
        ];
    }
}
