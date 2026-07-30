<?php

namespace App\Http\Controllers;

use App\Models\Contact_Us;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ContactUsService;

class ContactUsController extends Controller
{
    protected $service;
    private const CACHE_TTL = 1800;

    public function __construct(ContactUsService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            // Server-side pagination when ?page= is present
            if ($request->has('page')) {
                $perPage = min((int) $request->input('per_page', 12), 100);
                $page = (int) $request->input('page', 1);

                $paginated = Contact_Us::latest()
                    ->paginate($perPage, ['*'], 'page', $page);

                return response()->json([
                    'status' => 'success',
                    'data' => $paginated->items(),
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'last_page' => $paginated->lastPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'from' => $paginated->firstItem(),
                        'to' => $paginated->lastItem(),
                    ]
                ]);
            }

            // Return all (cached)
            $contacts = Cache::remember('api_contacts_all', self::CACHE_TTL, function () {
                return $this->service->index();
            });

            return response()->json(['status' => 'success', 'data' => $contacts]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch contact messages: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch contact messages'], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name', 'email', 'phone', 'subject', 'message', 'status']);
            $contact = $this->service->store($data);

            Cache::forget('api_contacts_all');

            return response()->json(['status' => 'success', 'data' => $contact, 'message' => 'Contact message created successfully'], 201);
        } catch (\Exception $e) {
            Log::error('Contact message creation failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Contact message creation failed'], 500);
        }
    }

    public function show($id)
    {
        try {
            $contact = Cache::remember('api_contact_' . $id, self::CACHE_TTL, function () use ($id) {
                return $this->service->show($id);
            });

            if (!$contact) return response()->json(['status' => 'error', 'message' => 'Contact message not found'], 404);
            return response()->json(['status' => 'success', 'data' => $contact]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch contact message: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch contact message'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'subject' => 'sometimes|string|max:255',
            'message' => 'sometimes|string',
            'status' => 'sometimes|string|in:pending,in_progress,completed'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['name', 'email', 'phone', 'subject', 'message', 'status']);
            $contact = $this->service->update($id, $data);
            if (!$contact) return response()->json(['status' => 'error', 'message' => 'Contact message not found'], 404);

            Cache::forget('api_contacts_all');
            Cache::forget('api_contact_' . $id);

            return response()->json(['status' => 'success', 'data' => $contact, 'message' => 'Contact message updated successfully']);
        } catch (\Exception $e) {
            Log::error('Contact message update failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Contact message update failed'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $res = $this->service->delete($id);
            if (!$res) return response()->json(['status' => 'error', 'message' => 'Contact message not found'], 404);

            Cache::forget('api_contacts_all');
            Cache::forget('api_contact_' . $id);

            return response()->json(['status' => 'success', 'message' => 'Contact message deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Contact message deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Contact message deletion failed'], 500);
        }
    }
}
