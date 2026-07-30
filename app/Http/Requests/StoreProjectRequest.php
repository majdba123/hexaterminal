<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'project_url' => 'nullable|url',
            'description' => 'required|string',
            'video_url' => 'nullable|url',
            'service_id' => 'required|exists:services,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'features' => 'nullable|array'
        ];
    }
}
