<?php

namespace App\Services;

use App\Models\Contact_Us;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactUsService
{
    public function index()
    {
        return Contact_Us::all();
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $contact = Contact_Us::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'status' => $data['status'] ?? 'pending'
            ]);
            DB::commit();
            return $contact;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ContactUsService store failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function show($id)
    {
        return Contact_Us::find($id);
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $contact = Contact_Us::find($id);
            if (! $contact) return null;
            $contact->update($data);
            DB::commit();
            return $contact;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ContactUsService update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $contact = Contact_Us::find($id);
            if (! $contact) return null;
            $contact->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ContactUsService delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
