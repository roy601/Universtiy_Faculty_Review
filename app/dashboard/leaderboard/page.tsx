"use client"

import { useEffect, useState } from "react"
import { DashboardLayout } from "@/components/layout/dashboard-layout"
import { ProtectedRoute } from "@/components/auth/protected-route"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Trophy, Medal, Award, Star } from "lucide-react"

export default function LeaderboardPage() {
  const [leaderboard, setLeaderboard] = useState([])
  const [category, setCategory] = useState("overall")
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchLeaderboard()
  }, [category])

  const fetchLeaderboard = async () => {
    try {
      const token = localStorage.getItem("auth_token")
      const response = await fetch(`/api/leaderboard?category=${category}&limit=20`, {
        headers: { Authorization: `Bearer ${token}` },
      })

      const data = await response.json()
      if (data.success) {
        setLeaderboard(data.data)
      }
    } catch (error) {
      console.error("Error fetching leaderboard:", error)
    } finally {
      setIsLoading(false)
    }
  }

  const getRankIcon = (position: number) => {
    switch (position) {
      case 1:
        return <Trophy className="h-6 w-6 text-yellow-500" />
      case 2:
        return <Medal className="h-6 w-6 text-gray-400" />
      case 3:
        return <Award className="h-6 w-6 text-amber-600" />
      default:
        return <span className="text-lg font-bold text-muted-foreground">#{position}</span>
    }
  }

  const getRatingValue = (faculty: any) => {
    switch (category) {
      case "teaching":
        return faculty.teaching_rating
      case "marking":
        return faculty.marking_rating
      case "behavior":
        return faculty.behavior_rating
      default:
        return faculty.overall_rating
    }
  }

  if (isLoading) {
    return (
      <ProtectedRoute requiredRole="student">
        <DashboardLayout activeTab="leaderboard">
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        </DashboardLayout>
      </ProtectedRoute>
    )
  }

  return (
    <ProtectedRoute requiredRole="student">
      <DashboardLayout activeTab="leaderboard">
        <div className="space-y-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 className="text-3xl font-bold text-foreground">Faculty Leaderboard</h1>
              <p className="text-muted-foreground">Top-rated faculty members</p>
            </div>
            <Select value={category} onValueChange={setCategory}>
              <SelectTrigger className="w-48">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="overall">Overall Rating</SelectItem>
                <SelectItem value="teaching">Teaching Quality</SelectItem>
                <SelectItem value="marking">Marking Fairness</SelectItem>
                <SelectItem value="behavior">Behavior & Attitude</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Star className="h-5 w-5" />
                Top Faculty - {category.charAt(0).toUpperCase() + category.slice(1)} Rating
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {leaderboard.map((faculty: any, index) => (
                  <div
                    key={faculty.initial}
                    className={`flex items-center gap-4 p-4 rounded-lg border ${
                      index < 3 ? "bg-accent/10 border-accent/20" : ""
                    }`}
                  >
                    <div className="flex items-center justify-center w-12">{getRankIcon(faculty.rank_position)}</div>

                    <div className="flex-1">
                      <div className="flex items-center gap-2 mb-1">
                        <h3 className="font-semibold">{faculty.name}</h3>
                        {faculty.dept_name && <Badge variant="secondary">{faculty.dept_name}</Badge>}
                      </div>
                      <p className="text-sm text-muted-foreground">{faculty.email}</p>
                    </div>

                    <div className="text-right">
                      <div className="text-2xl font-bold text-primary">{getRatingValue(faculty).toFixed(1)}</div>
                      <div className="text-sm text-muted-foreground">{faculty.total_reviews} reviews</div>
                    </div>
                  </div>
                ))}
              </div>

              {leaderboard.length === 0 && (
                <div className="text-center py-8">
                  <p className="text-muted-foreground">No faculty ratings available yet.</p>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </DashboardLayout>
    </ProtectedRoute>
  )
}
