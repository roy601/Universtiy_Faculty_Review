"use client"

import type React from "react"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { useAuth } from "@/contexts/auth-context"
import { useRouter } from "next/navigation"
import { LayoutDashboard, Users, MessageSquare, Trophy, Settings, LogOut, Menu, X, GraduationCap } from "lucide-react"

interface DashboardLayoutProps {
  children: React.ReactNode
  activeTab?: string
}

export function DashboardLayout({ children, activeTab = "dashboard" }: DashboardLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const { user, logout } = useAuth()
  const router = useRouter()

  const handleLogout = () => {
    logout()
    router.push("/login")
  }

  const navigation =
    user?.role === "admin"
      ? [
          { name: "Dashboard", href: "/admin", icon: LayoutDashboard, id: "dashboard" },
          { name: "Faculty Management", href: "/admin/faculty", icon: Users, id: "faculty" },
          { name: "Review Management", href: "/admin/reviews", icon: MessageSquare, id: "reviews" },
          { name: "Analytics", href: "/admin/analytics", icon: Trophy, id: "analytics" },
          { name: "Settings", href: "/admin/settings", icon: Settings, id: "settings" },
        ]
      : [
          { name: "Dashboard", href: "/dashboard", icon: LayoutDashboard, id: "dashboard" },
          { name: "Faculty", href: "/dashboard/faculty", icon: Users, id: "faculty" },
          { name: "My Reviews", href: "/dashboard/reviews", icon: MessageSquare, id: "reviews" },
          { name: "Leaderboard", href: "/dashboard/leaderboard", icon: Trophy, id: "leaderboard" },
        ]

  return (
    <div className="min-h-screen bg-background">
      {/* Mobile sidebar backdrop */}
      {sidebarOpen && (
        <div className="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden" onClick={() => setSidebarOpen(false)} />
      )}

      {/* Sidebar */}
      <div
        className={`fixed inset-y-0 left-0 z-50 w-64 bg-sidebar border-r border-sidebar-border transform transition-transform duration-300 ease-in-out lg:translate-x-0 ${
          sidebarOpen ? "translate-x-0" : "-translate-x-full"
        } lg:static lg:inset-0`}
      >
        <div className="flex flex-col h-full">
          {/* Header */}
          <div className="flex items-center justify-between p-4 border-b border-sidebar-border">
            <div className="flex items-center gap-2">
              <GraduationCap className="h-8 w-8 text-sidebar-accent" />
              <h1 className="text-lg font-bold text-sidebar-foreground">Faculty Review</h1>
            </div>
            <Button variant="ghost" size="sm" className="lg:hidden" onClick={() => setSidebarOpen(false)}>
              <X className="h-4 w-4" />
            </Button>
          </div>

          {/* User info */}
          <div className="p-4 border-b border-sidebar-border">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-sidebar-accent rounded-full flex items-center justify-center">
                <span className="text-sidebar-accent-foreground font-semibold">
                  {user?.name?.charAt(0).toUpperCase()}
                </span>
              </div>
              <div>
                <p className="font-medium text-sidebar-foreground">{user?.name}</p>
                <p className="text-sm text-muted-foreground capitalize">{user?.role}</p>
              </div>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex-1 p-4">
            <ul className="space-y-2">
              {navigation.map((item) => (
                <li key={item.name}>
                  <Button
                    variant={activeTab === item.id ? "secondary" : "ghost"}
                    className="w-full justify-start gap-3"
                    onClick={() => router.push(item.href)}
                  >
                    <item.icon className="h-4 w-4" />
                    {item.name}
                  </Button>
                </li>
              ))}
            </ul>
          </nav>

          {/* Logout */}
          <div className="p-4 border-t border-sidebar-border">
            <Button
              variant="ghost"
              className="w-full justify-start gap-3 text-destructive hover:text-destructive"
              onClick={handleLogout}
            >
              <LogOut className="h-4 w-4" />
              Logout
            </Button>
          </div>
        </div>
      </div>

      {/* Main content */}
      <div className="lg:pl-64">
        {/* Mobile header */}
        <div className="lg:hidden flex items-center justify-between p-4 border-b border-border bg-card">
          <Button variant="ghost" size="sm" onClick={() => setSidebarOpen(true)}>
            <Menu className="h-4 w-4" />
          </Button>
          <h1 className="font-semibold text-card-foreground">Faculty Review System</h1>
          <div className="w-8" /> {/* Spacer */}
        </div>

        {/* Page content */}
        <main className="p-4 lg:p-6">{children}</main>
      </div>
    </div>
  )
}
