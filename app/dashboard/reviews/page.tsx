"use client"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Star, Clock, CheckCircle } from "lucide-react"

export default function StudentReviewsPage() {
  const [reviews, setReviews] = useState([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchReviews()
  }, [])

  const fetchReviews = async () => {
    try {
      const token = localStorage.getItem("auth_token")
      const response = await fetch("/api/reviews", {
        headers: { Authorization: `Bearer ${token}` },
      })

      const data = await response.json()
      if (data.success) {
        setReviews(data.data)
      }
    } catch (error) {
      console.error("Error fetching reviews:", error)
    } finally {
      setIsLoading(false)
    }
  }

  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => (
      <Star
        key={i}
        className={`h-4 w-4 ${i < Math.floor(rating) ? "fill-accent text-accent" : "text-muted-foreground"}`}
      />
    ))
  }

  if (isLoading) {
    return (
      <ProtectedRoute requiredRole="student">
        <DashboardLayout activeTab="reviews">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="student">
      <DashboardLayout activeTab="reviews">
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold text-foreground">My Reviews</h1>
            <p className="text-muted-foreground">View and manage your faculty reviews</p>
          </div>

          <div className="grid gap-4">
            {reviews.map((review: any) => (
              <Card key={review.id}>
                <CardHeader>
                  <div className="flex items-start justify-between">
                    <div>
                      <CardTitle className="text-lg">{review.faculty_name}</CardTitle>
                      <div className="flex items-center gap-2 mt-1">
                        <Badge variant="secondary">{review.course_code}</Badge>
                        <Badge variant="outline">{review.semester}</Badge>
                        <Badge variant={review.is_approved ? "default" : "secondary"}>
                          {review.is_approved ? (
                            <>
                              <CheckCircle className="h-3 w-3 mr-1" />
                              Approved
                            </>
                          ) : (
                            <>
                              <Clock className="h-3 w-3 mr-1" />
                              Pending
                            </>
                          )}
                        </Badge>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="flex items-center gap-1">{renderStars(review.overall_rating)}</div>
                      <p className="text-sm text-muted-foreground mt-1">{review.overall_rating.toFixed(1)}/5</p>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="grid grid-cols-3 gap-4 text-sm">
                      <div>
                        <p className="font-medium text-card-foreground">Teaching</p>
                        <div className="flex items-center gap-1 mt-1">
                          {renderStars(review.teaching_rating)}
                          <span className="text-muted-foreground ml-1">{review.teaching_rating}/5</span>
                        </div>
                      </div>
                      <div>
                        <p className="font-medium text-card-foreground">Marking</p>
                        <div className="flex items-center gap-1 mt-1">
                          {renderStars(review.marking_rating)}
                          <span className="text-muted-foreground ml-1">{review.marking_rating}/5</span>
                        </div>
                      </div>
                      <div>
                        <p className="font-medium text-card-foreground">Behavior</p>
                        <div className="flex items-center gap-1 mt-1">
                          {renderStars(review.behavior_rating)}
                          <span className="text-muted-foreground ml-1">{review.behavior_rating}/5</span>
                        </div>
                      </div>
                    </div>

                    {review.comment && (
                      <div>
                        <p className="font-medium text-card-foreground mb-2">Comment</p>
                        <p className="text-muted-foreground text-sm bg-muted p-3 rounded-md">{review.comment}</p>
                      </div>
                    )}

                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                      <span>Submitted on {new Date(review.created_at).toLocaleDateString()}</span>
                      {review.approved_at && (
                        <span>Approved on {new Date(review.approved_at).toLocaleDateString()}</span>
                      )}
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          {reviews.length === 0 && (
            <Card>
              <CardContent className="text-center py-12">
                <p className="text-muted-foreground mb-4">You haven't submitted any reviews yet.</p>
                <Button onClick={() => (window.location.href = "/dashboard")}>Browse Faculty to Review</Button>
              </CardContent>
            </Card>
          )}
        </div>
      </DashboardLayout>
    </ProtectedRoute>
  )
}
