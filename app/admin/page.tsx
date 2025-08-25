"use client"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { StatsCards } from "@/components/dashboard/stats-cards"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { CheckCircle, Clock, Users, MessageSquare } from "lucide-react"

export default function AdminDashboard() {
  const [stats, setStats] = useState({ totalFaculty: 0, totalReviews: 0, averageRating: 0, pendingReviews: 0 })
  const [pendingReviews, setPendingReviews] = useState([])
  const [recentActivity, setRecentActivity] = useState([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchData()
  }, [])

  const fetchData = async () => {
    try {
      const token = localStorage.getItem("auth_token")
      const headers = { Authorization: `Bearer ${token}` }

      // Fetch stats
      const statsRes = await fetch("/api/dashboard/stats", { headers })
      const statsData = await statsRes.json()
      if (statsData.success) setStats(statsData.data)

      // Fetch pending reviews
      const reviewsRes = await fetch("/api/reviews?pending=true", { headers })
      const reviewsData = await reviewsRes.json()
      if (reviewsData.success) setPendingReviews(reviewsData.data)
    } catch (error) {
      console.error("Error fetching data:", error)
    } finally {
      setIsLoading(false)
    }
  }

  const handleApproveReview = async (reviewId: number) => {
    try {
      const token = localStorage.getItem("auth_token")
      const response = await fetch(`/api/reviews/${reviewId}/approve`, {
        method: "PUT",
        headers: { Authorization: `Bearer ${token}` },
      })

      const data = await response.json()
      if (data.success) {
        // Refresh data
        fetchData()
      } else {
        alert(data.error || "Failed to approve review")
      }
    } catch (error) {
      console.error("Error approving review:", error)
      alert("Failed to approve review")
    }
  }

  if (isLoading) {
    return (
      <ProtectedRoute requiredRole="admin">
        <DashboardLayout activeTab="dashboard">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="admin">
      <DashboardLayout activeTab="dashboard">
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Admin Dashboard</h1>
            <p className="text-muted-foreground">Manage faculty reviews and system administration</p>
          </div>

          <StatsCards stats={stats} />

          <div className="grid gap-6 lg:grid-cols-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="h-5 w-5" />
                  Pending Reviews ({pendingReviews.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {pendingReviews.slice(0, 5).map((review: any) => (
                    <div key={review.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <span className="font-medium">{review.faculty_name}</span>
                          <Badge variant="secondary">{review.course_code}</Badge>
                        </div>
                        <p className="text-sm text-muted-foreground">
                          By {review.student_name} • {review.semester}
                        </p>
                        <div className="flex items-center gap-4 mt-2 text-sm">
                          <span>Teaching: {review.teaching_rating}/5</span>
                          <span>Marking: {review.marking_rating}/5</span>
                          <span>Behavior: {review.behavior_rating}/5</span>
                        </div>
                      </div>
                      <Button size="sm" onClick={() => handleApproveReview(review.id)} className="ml-4">
                        <CheckCircle className="h-4 w-4 mr-1" />
                        Approve
                      </Button>
                    </div>
                  ))}
                  {pendingReviews.length === 0 && (
                    <div className="text-center py-8">
                      <p className="text-muted-foreground">No pending reviews</p>
                    </div>
                  )}
                  {pendingReviews.length > 5 && (
                    <Button variant="outline" className="w-full bg-transparent">
                      View All Pending Reviews
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <MessageSquare className="h-5 w-5" />
                  Quick Actions
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid gap-3">
                  <Button variant="outline" className="justify-start bg-transparent">
                    <Users className="h-4 w-4 mr-2" />
                    Manage Faculty
                  </Button>
                  <Button variant="outline" className="justify-start bg-transparent">
                    <MessageSquare className="h-4 w-4 mr-2" />
                    Review Management
                  </Button>
                  <Button variant="outline" className="justify-start bg-transparent">
                    <CheckCircle className="h-4 w-4 mr-2" />
                    System Settings
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </DashboardLayout>
    </ProtectedRoute>
  )
}
