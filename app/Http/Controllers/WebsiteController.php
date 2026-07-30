<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * Home page - all data loaded via Axios from API routes.
     */
    public function index()
    {
        return view('website.index');
    }

    /**
     * All projects page - data loaded via Axios.
     */
    public function projects()
    {
        return view('website.projects');
    }

    /**
     * Project detail page - data loaded via Axios.
     */
    public function projectDetail($id)
    {
        return view('website.project-detail', compact('id'));
    }

    /**
     * Team member detail page - data loaded via Axios.
     */
    public function teamDetail($id)
    {
        return view('website.team-detail', compact('id'));
    }

    /**
     * Service detail page - shows service info + projects by service_id.
     */
    public function serviceDetail($id)
    {
        return view('website.service-detail', compact('id'));
    }
}
