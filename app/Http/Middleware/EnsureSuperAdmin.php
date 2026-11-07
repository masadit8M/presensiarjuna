<?php

namespace App\Http\Middleware;

use App\Http\Controllers\GajiController;
use Closure;
use Illuminate\Http\Request;

/**
 * Membatasi route hanya untuk Superadmin (masaditfb@gmail.com, guard 'user').
 * Dipakai untuk seluruh modul yang berhubungan dengan gaji (Penggajian, Master Gaji, Slip Gaji, Pinjaman/Kasbon).
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!GajiController::isSuperAdmin()) {
            if ($request->expectsJson()) {
                abort(403, 'Akses ditolak. Hanya Superadmin.');
            }

            return redirect('/panel/dashboardadmin')->with([
                'warning' => 'Akses ditolak. Modul Gaji & Slip Gaji hanya dapat diakses oleh Superadmin.',
            ]);
        }

        return $next($request);
    }
}
