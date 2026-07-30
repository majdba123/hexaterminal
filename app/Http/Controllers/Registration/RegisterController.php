<?php

namespace App\Http\Controllers\Registration;

use App\Helpers\OtpHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\registartion\RegisterUser; // Ensure the namespace is correct
use App\Models\User; // Ensure the namespace is correct
use App\Services\registartion\register;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Cache facade
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

class RegisterController extends Controller
{
    protected $userService;

    public function __construct(register $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Handle the registration of a new user.
     */
    public function register(RegisterUser $request): JsonResponse
    {
        $validatedData = $request->validated();

        // Pass the modified request data to the service
        $user = $this->userService->register($validatedData);

        if (isset($validatedData['email'])) {
            OtpHelper::sendOtpEmail($user->id);
        }/*elseif(isset($validatedData['phone']))
        {
            $this->sendOtp_mobile($user->id);
        }*/

        return response()->json([
            'message' => 'User  registered successfully',
            'user' => $user,
        ], 201);
    }

    public function resendOtp()
    {
        $user = Auth::user();

        // إذا كان هناك OTP موجود وفعال، لا نرسل جديد
        if ($user->otp == 1) {
            return response()->json([
                'message' => 'you are  active OTP already ',
            ], 201);
        }
        OtpHelper::sendOtpEmail($user->id);

        // إذا لم يكن هناك OTP أو انتهت صلاحيته، نرسل جديد
        return response()->json([
            'message' => 'OTP send successfully',
        ], 201);
    }

    private function sendOtp_mobile($id)
    {
        $user = User::findOrFail($id);
        $otp = Str::random(6);

        // Store OTP in cache with ID and phone number, set expiration time (e.g., 5 minutes)
        Cache::put('otp_'.$user->id, ['otp' => $otp, 'phone' => $user->phone], 300);

        // Send OTP via SMS
        $this->sendSms($user->phone, $otp);

        return $otp;
    }

    public function verfication_otp(Request $request)
    {
        // Validate the request
        $request->validate([
            'otp' => 'required|string|size:6', // Ensure OTP is a string of size 6
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (! $user) {
            return response()->json(['message' => 'User  not authenticated.'], 401);
        }

        try {
            // Call the verifyOtp method from the service
            $this->userService->verifyOtp($request->input('otp'), $user);

            return response()->json(['message' => 'OTP verified successfully.'], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('OTP verification failed: '.$e->getMessage());

            return response()->json(['message' => 'OTP verification failed.'], 400);
        }
    }

    private function sendSms($phone, $otp)
    {
        $account_sid = env('TWILIO_SID');
        $auth_token = env('TWILIO_TOKEN');
        $twilio_number = env('TWILIO_FROM');

        $client = new Client($account_sid, $auth_token);
        $client->messages->create($phone, [
            'from' => $twilio_number,
            'body' => "Your OTP code is: $otp",
        ]);
    }
}
