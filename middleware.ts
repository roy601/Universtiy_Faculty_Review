import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"

export function middleware(request: NextRequest) {
  const protectedRoutes = ["/dashboard", "/admin"]
  const adminRoutes = ["/admin"]

  const isProtectedRoute = protectedRoutes.some((route) => request.nextUrl.pathname.startsWith(route))
  const isAdminRoute = adminRoutes.some((route) => request.nextUrl.pathname.startsWith(route))

  // For protected routes, redirect to login if no user data in client
  if (isProtectedRoute) {
    // Note: Server-side middleware can't access localStorage
    // Client-side protection will be handled by ProtectedRoute component
    return NextResponse.next()
  }

  return NextResponse.next()
}

export const config = {
  matcher: ["/dashboard/:path*", "/admin/:path*"],
}
