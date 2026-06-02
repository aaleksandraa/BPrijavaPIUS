<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = AdminUser::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Neispravni podaci za prijavu.'],
            ]);
        }

        if (!$admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Vaš nalog nije aktivan.'],
            ]);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $admin->id,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
            'token' => $token,
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = AdminUser::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if ($admin) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $admin->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $resetUrl = $this->frontendUrl($request)
                . '/admin/reset-password?email=' . urlencode($admin->email)
                . '&token=' . urlencode($token);

            Mail::raw(
                "Pozdrav,\n\nZa reset lozinke PIUS admin naloga otvorite link:\n\n{$resetUrl}\n\nLink vrijedi 60 minuta. Ako niste trazili reset lozinke, ignorisite ovaj email.",
                fn ($message) => $message
                    ->to($admin->email)
                    ->subject('PIUS Admin - reset lozinke')
            );
        }

        return response()->json([
            'message' => 'Ako nalog postoji, poslali smo instrukcije za reset lozinke.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (
            !$resetToken ||
            Carbon::parse($resetToken->created_at)->addMinutes(60)->isPast() ||
            !Hash::check($request->token, $resetToken->token)
        ) {
            throw ValidationException::withMessages([
                'email' => ['Link za reset lozinke nije ispravan ili je istekao.'],
            ]);
        }

        $admin = AdminUser::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Link za reset lozinke nije ispravan ili je istekao.'],
            ]);
        }

        $admin->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        $admin->tokens()->delete();
        DB::table('password_reset_tokens')->where('email', $admin->email)->delete();

        return response()->json([
            'message' => 'Lozinka je uspjesno promijenjena. Mozete se prijaviti.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Uspješno ste se odjavili.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    private function frontendUrl(Request $request): string
    {
        $origin = $request->headers->get('origin');

        if ($origin) {
            return rtrim($origin, '/');
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        return str_replace('://api.', '://', $appUrl);
    }
}
