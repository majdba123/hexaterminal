<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use CachesPublicResponses;

    public function index(Request $request)
    {
        $featured = $request->boolean('featured');
        $suffix = 'featured:'.($featured ? '1' : '0');

        $testimonials = $this->rememberList('testimonials', $suffix, function () use ($featured) {
            $query = Testimonial::approved()->orderByDesc('given_at');

            if ($featured) {
                $query->featured();
            }

            return $query->get();
        });

        return response()->json(['data' => TestimonialResource::collection($testimonials)]);
    }
}
