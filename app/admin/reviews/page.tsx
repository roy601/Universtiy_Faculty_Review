"use client"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Star, Clock, CheckCircle, Search, Filter } from "lucide-react"

export default function AdminReviewsPage() {
  const [pendingReviews, setPendingReviews] = useState([])
  const [approvedReviews, setApprovedReviews] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [filterFaculty, setFilterFaculty] = useState("all")
  const [faculty, setFaculty] = useState([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchData()
  }, [])

  const fetchData = async () => {
    try {
      const token = localStorage.getItem("auth_token")
      const headers = { Authorization: `Bearer ${token}` }

      const [pendingRes, approvedRes, facultyRes] = await Promise.all([
        fetch("/api/reviews?pending=true", { headers }),
        fetch("/api/reviews", { headers }),
        fetch("/api/faculty", { headers }),
      ])

      const [pendingData, approvedData, facultyData] = await Promise.all([
        pendingRes.json(),
        approvedRes.json(),
        facultyRes.json(),
      ])

      if (pendingData.success) setPendingReviews(pendingData.data)
      if (approvedData.success) setApprovedReviews(approvedData.data.filter((r: any) => r.is_approved))
      if (facultyData.success) setFaculty(facultyData.data)
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
        fetchData() // Refresh data
      } else {
        alert(data.error || "Failed to approve review")
      }
    } catch (error) {
      console.error("Error approving review:", error)
      alert("Failed to approve review")
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

  const filterReviews = (reviews: any[]) => {
    return reviews.filter((review) => {
      const matchesSearch =
        review.faculty_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        review.student_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        review.course_code.toLowerCase().includes(searchTerm.toLowerCase())
      const matchesFaculty = filterFaculty === "all" || review.faculty_initial === filterFaculty
      return matchesSearch && matchesFaculty
    })
  }

  const ReviewCard = ({ review, showApproveButton = false }: { review: any; showApproveButton?: boolean }) => (
    <Card key={review.id}>
      <CardHeader>
        <div className="flex items-start justify-between">
          <div>
            <CardTitle className="text-lg">{review.faculty_name}</CardTitle>
            <div className="flex items-center gap-2 mt-1">
              <Badge variant="secondary">{review.course_code}</Badge>
              <Badge variant="outline">{review.semester}</Badge>
              <Badge variant="secondary">By {review.student_name}</Badge>
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

          <div className="flex items-center justify-between">
            <span className="text-sm text-muted-foreground">
              Submitted on {new Date(review.created_at).toLocaleDateString()}
            </span>
            {showApproveButton && (
              <Button onClick={() => handleApproveReview(review.id)} size="sm">
                <CheckCircle className="h-4 w-4 mr-1" />
                Approve Review
              </Button>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  )

  if (isLoading) {
    return (
      <ProtectedRoute requiredRole="admin">
        <DashboardLayout activeTab="reviews">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="admin">
      <DashboardLayout activeTab="reviews">
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Review Management</h1>
            <p className="text-muted-foreground">Manage and approve faculty reviews</p>
          </div>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Filter className="h-5 w-5" />
                Filter Reviews
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col sm:flex-row gap-4">
                <div className="relative flex-1">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search by faculty, student, or course..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
                <Select value={filterFaculty} onValueChange={setFilterFaculty}>
                  <SelectTrigger className="w-full sm:w-48">
                    <SelectValue placeholder="All Faculty" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Faculty</SelectItem>
                    {faculty.map((f: any) => (
                      <SelectItem key={f.initial} value={f.initial}>
                        {f.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </CardContent>
          </Card>

          <Tabs defaultValue="pending" className="space-y-4">
            <TabsList>
              <TabsTrigger value="pending" className="flex items-center gap-2">
                <Clock className="h-4 w-4" />
                Pending ({filterReviews(pendingReviews).length})
              </TabsTrigger>
              <TabsTrigger value="approved" className="flex items-center gap-2">
                <CheckCircle className="h-4 w-4" />
                Approved ({filterReviews(approvedReviews).length})
              </TabsTrigger>
            </TabsList>

            <TabsContent value="pending" className="space-y-4">
              {filterReviews(pendingReviews).map((review) => (
                <ReviewCard key={review.id} review={review} showApproveButton={true} />
              ))}
              {filterReviews(pendingReviews).length === 0 && (
                <Card>
                  <CardContent className="text-center py-12">
                    <p className="text-muted-foreground">No pending reviews found.</p>
                  </CardContent>
                </Card>
              )}
            </TabsContent>

            <TabsContent value="approved" className="space-y-4">
              {filterReviews(approvedReviews).map((review) => (
                <ReviewCard key={review.id} review={review} />
              ))}
              {filterReviews(approvedReviews).length === 0 && (
                <Card>
                  <CardContent className="text-center py-12">
                    <p className="text-muted-foreground">No approved reviews found.</p>
                  </CardContent>
                </Card>
              )}
            </TabsContent>
          </Tabs>
        </div>
      </DashboardLayout>
    </ProtectedRoute>
  )
}
