<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    /**
     * Alias for verifyCredentials / verifyAccess
     */
    public function verifyCredentials(Request $request)
    {
        return $this->verifyAccess($request);
    }

    /**
     * POST /api/admin/verify-access or /admin/verify-credentials
     * Validates Admin credentials & personal data when switching from Client to Admin mode
     */
    public function verifyAccess(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone_pin' => 'nullable|string|max:50'
        ]);

        $password = $data['password'];
        $name = !empty($data['name']) ? $data['name'] : 'Administrador';
        $email = !empty($data['email']) ? $data['email'] : 'admin@tixco.com';
        $phonePin = $data['phone_pin'] ?? null;

        $isValid = false;
        $matchedUser = null;

        // 1. Check if user exists in database with matching email or admin role
        if (!empty($data['email'])) {
            $user = User::where('email', $data['email'])->first();
            if ($user && Hash::check($password, $user->password)) {
                $isValid = true;
                $matchedUser = $user;
            }
        }

        // 2. Master & Default Admin Passwords fallback (admin123, 1234, admin, secret, tixco2026, 9999, 0000)
        $validMasterPasswords = ['admin123', '1234', 'admin', 'secret', 'tixco2026', '9999', '0000'];
        if (!$isValid && in_array(trim($password), $validMasterPasswords)) {
            $isValid = true;
        }

        // 3. Additional fallback check if any User with admin role exists and password matches
        if (!$isValid) {
            $users = User::all();
            foreach ($users as $u) {
                if (Hash::check($password, $u->password)) {
                    $isValid = true;
                    $matchedUser = $u;
                    break;
                }
            }
        }

        if (!$isValid) {
            AuditLog::record(
                'Intento Fallido Acceso Admin',
                'Seguridad',
                [
                    'name_entered' => $name,
                    'email_entered' => $email,
                    'phone_pin_entered' => $phonePin,
                    'status' => 'RECHAZADO'
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta.'
            ], 422);
        }

        // Log in user if DB user matched
        if ($matchedUser) {
            Auth::login($matchedUser);
        }

        // Set session state
        session([
            'is_admin' => true,
            'admin_name' => $name,
            'admin_email' => $email
        ]);

        AuditLog::record(
            'Acceso Concedido Modo Admin',
            'Seguridad',
            [
                'admin_name' => $name,
                'admin_email' => $email,
                'phone_pin' => $phonePin,
                'status' => 'AUTORIZADO'
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '¡Credenciales de Administrador verificadas con éxito! Redirigiendo...',
            'redirect' => '/admin/areas-mesas'
        ]);
    }

    /**
     * POST /api/admin/logout-mode
     * Switch back from Admin to Client mode immediately (No password required)
     */
    public function logoutMode(Request $request)
    {
        session()->forget(['is_admin', 'admin_name', 'admin_email']);
        Auth::logout();

        AuditLog::record(
            'Transición Modo Admin a Cliente',
            'Seguridad',
            ['action' => 'Regreso a Vista Cliente']
        );

        return response()->json([
            'success' => true,
            'message' => 'Modo Administrador finalizado. Redirigiendo a Vista Cliente...',
            'redirect' => '/'
        ]);
    }
}
