"use client"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { StatsCards } from "@/components/dashboard/stats-cards"
import { FacultyCard } from "@/components/faculty/faculty-card"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { ReviewForm } from "@/components/reviews/review-form"
import { Search } from "lucide-react"

export default function StudentDashboard() {
  const [stats, setStats] = useState({ totalFaculty: 0, totalReviews: 0, averageRating: 0 })
  const [faculty, setFaculty] = useState([])
  const [departments, setDepartments] = useState([])
  const [courses, setCourses] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [selectedDept, setSelectedDept] = useState("all") // Updated default value to "all"
  const [showReviewForm, setShowReviewForm] = useState(false)
  const [selectedFaculty, setSelectedFaculty] = useState<any>(null)
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

      // Fetch faculty
      const facultyRes = await fetch("/api/faculty", { headers })
      const facultyData = await facultyRes.json()
      if (facultyData.success) setFaculty(facultyData.data)

      // Fetch departments
      const deptRes = await fetch("/api/departments", { headers })
      const deptData = await deptRes.json()
      if (deptData.success) setDepartments(deptData.data)

      // Fetch courses
      const coursesRes = await fetch("/api/courses", { headers })
      const coursesData = await coursesRes.json()
      if (coursesData.success) setCourses(coursesData.data)
    } catch (error) {
      console.error("Error fetching data:", error)
    } finally {
      setIsLoading(false)
    }
  }

  const handleReviewSubmit = async (reviewData: any) => {
    try {
      const token = localStorage.getItem("auth_token")
      const response = await fetch("/api/reviews", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(reviewData),
      })

      const data = await response.json()
      if (data.success) {
        setShowReviewForm(false)
        setSelectedFaculty(null)
        // Refresh data
        fetchData()
      } else {
        alert(data.error || "Failed to submit review")
      }
    } catch (error) {
      console.error("Error submitting review:", error)
      alert("Failed to submit review")
    }
  }

  const filteredFaculty = faculty.filter((f: any) => {
    const matchesSearch =
      f.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      f.email.toLowerCase().includes(searchTerm.toLowerCase())
    const matchesDept = selectedDept === "all" || f.dept_id?.toString() === selectedDept
    return matchesSearch && matchesDept
  })

  if (isLoading) {
    return (
      <ProtectedRoute requiredRole="student">
        <DashboardLayout activeTab="dashboard">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  if (showReviewForm && selectedFaculty) {
    return (
      <ProtectedRoute requiredRole="student">
        <DashboardLayout activeTab="dashboard">
          <ReviewForm
            facultyInitial={selectedFaculty.initial}
            facultyName={selectedFaculty.name}
            courses={courses.filter((c: any) => c.faculty_initial === selectedFaculty.initial)}
            onSubmit={handleReviewSubmit}
            onCancel={() => {
              setShowReviewForm(false)
              setSelectedFaculty(null)
            }}
          />
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="student">
      <DashboardLayout activeTab="dashboard">
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Student Dashboard</h1>
            <p className="text-muted-foreground">Review and rate faculty members</p>
          </div>

          <StatsCards stats={stats} />

          <Card>
            <CardHeader>
              <CardTitle>Faculty Directory</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col sm:flex-row gap-4 mb-6">
                <div className="relative flex-1">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search faculty..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-10"
                  />
                </div>
                <Select value={selectedDept} onValueChange={setSelectedDept}>
                  <SelectTrigger className="w-full sm:w-48">
                    <SelectValue placeholder="All Departments" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">All Departments</SelectItem> {/* Updated value to "all" */}
                    {departments.map((dept: any) => (
                      <SelectItem key={dept.id} value={dept.id.toString()}>
                        {dept.dept_name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                {filteredFaculty.map((facultyMember: any) => (
                  <FacultyCard
                    key={facultyMember.initial}
                    faculty={facultyMember}
                    onReview={(initial) => {
                      setSelectedFaculty(facultyMember)
                      setShowReviewForm(true)
                    }}
                  />
                ))}
              </div>

              {filteredFaculty.length === 0 && (
                <div className="text-center py-8">
                  <p className="text-muted-foreground">No faculty members found matching your criteria.</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </DashboardLayout>
    </ProtectedRoute>
  )
}
