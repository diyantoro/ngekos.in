<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Titik masuk dashboard setelah login.
     * Arahkan ke dashboard spesifik sesuai role user.
     */
    public function __invoke(Request $request)
    {
        $routeByRole = [
            'super_admin' => 'dashboard.super-admin',
            'pemilik' => 'dashboard.pemilik',
            'admin' => 'dashboard.admin',
            'anak_kos' => 'dashboard.anak-kos',
        ];

        $role = $request->user()->getRoleNames()->first();
        $route = $routeByRole[$role] ?? 'dashboard.anak-kos';

        return redirect()->route($route);
    }
}
