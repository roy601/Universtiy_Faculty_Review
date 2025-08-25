"use client"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { FacultyCard } from "@/components/faculty/faculty-card"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Search } from "lucide-react"

export default function StudentFacultyPage() {
  const [faculty, setFaculty] = useState([])
  const [departments, setDepartments] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [selectedDept, setSelectedDept] = useState("all") // Updated default value to "all"
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchData()
  }, [])

  const fetchData = async () => {
    try {
      const token = localStorage.getItem("auth_token")
      const headers = { Authorization: `Bearer ${token}` }

      const [facultyRes, deptRes] = await Promise.all([
        fetch("/api/faculty", { headers }),
        fetch("/api/departments", { headers }),
      ])

      const [facultyData, deptData] = await Promise.all([facultyRes.json(), deptRes.json()])

      if (facultyData.success) setFaculty(facultyData.data)
      if (deptData.success) setDepartments(deptData.data)
    } catch (error) {
      console.error("Error fetching data:", error)
    } finally {
      setIsLoading(false)
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
        <DashboardLayout activeTab="faculty">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="student">
      <DashboardLayout activeTab="faculty">
        <div className="space-y-6">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Faculty Directory</h1>
            <p className="text-muted-foreground">Browse and review faculty members</p>
          </div>

          <Card>
            <CardHeader>
              <CardTitle>Search Faculty</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col sm:flex-row gap-4 mb-6">
                <div className="relative flex-1">
                  <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search by name or email..."
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
                    onReview={() => {
                      /* Handle review */
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
