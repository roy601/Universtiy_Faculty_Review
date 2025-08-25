"use client"

import type React from "react"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { FacultyCard } from "@/components/faculty/faculty-card"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Plus, Search } from "lucide-react"

export default function AdminFacultyPage() {
  const [faculty, setFaculty] = useState([])
  const [departments, setDepartments] = useState([])
  const [searchTerm, setSearchTerm] = useState("")
  const [selectedDept, setSelectedDept] = useState("all")
  const [showAddDialog, setShowAddDialog] = useState(false)
  const [isLoading, setIsLoading] = useState(true)
  const [newFaculty, setNewFaculty] = useState({
    initial: "",
    name: "",
    email: "",
    room_no: "",
    specific_history: "",
    dept_id: "",
  })

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

  const handleAddFaculty = async (e: React.FormEvent) => {
    e.preventDefault()
    try {
      const token = localStorage.getItem("auth_token")
      const response = await fetch("/api/faculty", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(newFaculty),
      })

      const data = await response.json()
      if (data.success) {
        setShowAddDialog(false)
        setNewFaculty({
          initial: "",
          name: "",
          email: "",
          room_no: "",
          specific_history: "",
          dept_id: "",
        })
        fetchData() // Refresh data
      } else {
        alert(data.error || "Failed to add faculty")
      }
    } catch (error) {
      console.error("Error adding faculty:", error)
      alert("Failed to add faculty")
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
      <ProtectedRoute requiredRole="admin">
        <DashboardLayout activeTab="faculty">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="admin">
      <DashboardLayout activeTab="faculty">
        <div className="space-y-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold text-foreground">Faculty Management</h1>
              <p className="text-muted-foreground">Manage faculty members and their information</p>
            </div>
            <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
              <DialogTrigger asChild>
                <Button>
                  <Plus className="h-4 w-4 mr-2" />
                  Add Faculty
                </Button>
              </DialogTrigger>
              <DialogContent className="max-w-md">
                <DialogHeader>
                  <DialogTitle>Add New Faculty</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleAddFaculty} className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="initial">Faculty Initial</Label>
                    <Input
                      id="initial"
                      value={newFaculty.initial}
                      onChange={(e) => setNewFaculty({ ...newFaculty, initial: e.target.value })}
                      placeholder="e.g., JD"
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="name">Full Name</Label>
                    <Input
                      id="name"
                      value={newFaculty.name}
                      onChange={(e) => setNewFaculty({ ...newFaculty, name: e.target.value })}
                      placeholder="e.g., Dr. John Doe"
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Email</Label>
                    <Input
                      id="email"
                      type="email"
                      value={newFaculty.email}
                      onChange={(e) => setNewFaculty({ ...newFaculty, email: e.target.value })}
                      placeholder="john.doe@university.edu"
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="room_no">Room Number</Label>
                    <Input
                      id="room_no"
                      value={newFaculty.room_no}
                      onChange={(e) => setNewFaculty({ ...newFaculty, room_no: e.target.value })}
                      placeholder="e.g., CS-101"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="dept_id">Department</Label>
                    <Select
                      value={newFaculty.dept_id}
                      onValueChange={(value) => setNewFaculty({ ...newFaculty, dept_id: value })}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select department" />
                      </SelectTrigger>
                      <SelectContent>
                        {departments.map((dept: any) => (
                          <SelectItem key={dept.id} value={dept.id.toString()}>
                            {dept.dept_name}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="specific_history">Background</Label>
                    <Textarea
                      id="specific_history"
                      value={newFaculty.specific_history}
                      onChange={(e) => setNewFaculty({ ...newFaculty, specific_history: e.target.value })}
                      placeholder="PhD, experience, specializations..."
                      rows={3}
                    />
                  </div>
                  <div className="flex gap-2">
                    <Button type="submit" className="flex-1">
                      Add Faculty
                    </Button>
                    <Button type="button" variant="outline" onClick={() => setShowAddDialog(false)}>
                      Cancel
                    </Button>
                  </div>
                </form>
              </DialogContent>
            </Dialog>
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
                    <SelectItem value="all">All Departments</SelectItem>
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
                  <FacultyCard key={facultyMember.initial} faculty={facultyMember} showReviewButton={false} />
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
