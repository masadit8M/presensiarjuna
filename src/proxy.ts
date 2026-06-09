import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { decrypt } from '@/lib/session';

// Define route categories
const EMPLOYEE_ROUTES = ['/dashboard', '/presensi', '/editprofile'];
const ADMIN_ROUTES = ['/panel/dashboardadmin', '/karyawan', '/cabang', '/departemen', '/jamkerja', '/monitoring', '/rekap', '/laporan'];
const AUTH_ROUTES = ['/login', '/panel'];

export async function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;
  
  // Get token from cookies
  const sessionToken = request.cookies.get('epresensi_session')?.value;
  const session = sessionToken ? await decrypt(sessionToken) : null;

  // 1. Handle Employee routes
  const isEmployeeRoute = EMPLOYEE_ROUTES.some(route => pathname.startsWith(route));
  if (isEmployeeRoute) {
    if (!session || session.role !== 'employee') {
      return NextResponse.redirect(new URL('/login', request.url));
    }
  }

  // 2. Handle Admin routes
  const isAdminRoute = ADMIN_ROUTES.some(route => pathname.startsWith(route));
  if (isAdminRoute) {
    if (!session || (session.role !== 'administrator' && session.role !== 'admin departemen')) {
      return NextResponse.redirect(new URL('/panel', request.url));
    }
  }

  // 3. Handle Auth routes (Login / Panel Login)
  const isAuthRoute = AUTH_ROUTES.some(route => pathname === route);
  if (isAuthRoute) {
    if (session) {
      if (session.role === 'employee') {
        return NextResponse.redirect(new URL('/dashboard', request.url));
      } else {
        return NextResponse.redirect(new URL('/panel/dashboardadmin', request.url));
      }
    }
  }

  // Root redirect
  if (pathname === '/') {
    if (session) {
      if (session.role === 'employee') {
        return NextResponse.redirect(new URL('/dashboard', request.url));
      } else {
        return NextResponse.redirect(new URL('/panel/dashboardadmin', request.url));
      }
    } else {
      return NextResponse.redirect(new URL('/login', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     * - icon-512.png, robots.txt, manifest.json, service-worker.js (public root files)
     */
    '/((?!api|_next/static|_next/image|favicon.ico|icon-512.png|robots.txt|manifest.json|service-worker.js).*)',
  ],
};
