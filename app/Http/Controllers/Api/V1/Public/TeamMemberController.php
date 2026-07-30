<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\TeamMemberResource;
use App\Models\TeamMember;

class TeamMemberController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $team = $this->rememberList('team', 'all', function () {
            return TeamMember::published()->with('claims')->orderBy('sort_order')->get();
        });

        return response()->json(['data' => TeamMemberResource::collection($team)]);
    }

    public function show(string $slug)
    {
        $member = $this->rememberShow('team', $slug, function () use ($slug) {
            return TeamMember::published()->with('claims')->where('slug', $slug)->first();
        });

        abort_if(! $member, 404);

        return response()->json(['data' => new TeamMemberResource($member)]);
    }
}
