"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Star, MapPin, MessageSquare } from "lucide-react"

interface FacultyCardProps {
  faculty: {
    initial: string
    name: string
    email: string
    room_no?: string
    overall_rating: number
    total_reviews: number
    behavior_rating: number
    marking_rating: number
    teaching_rating: number
    dept_name?: string
  }
  onReview?: (facultyInitial: string) => void
  showReviewButton?: boolean
}

export function FacultyCard({ faculty, onReview, showReviewButton = true }: FacultyCardProps) {
  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => (
      <Star
        key={i}
        className={`h-4 w-4 ${i < Math.floor(rating) ? "fill-accent text-accent" : "text-muted-foreground"}`}
      />
    ))
  }

  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardHeader>
        <div className="flex items-start justify-between">
          <div>
            <CardTitle className="text-lg">{faculty.name}</CardTitle>
            <p className="text-sm text-muted-foreground">{faculty.email}</p>
            {faculty.dept_name && (
              <Badge variant="secondary" className="mt-2">
                {faculty.dept_name}
              </Badge>
            )}
          </div>
          <div className="text-right">
            <div className="flex items-center gap-1">{renderStars(faculty.overall_rating)}</div>
            <p className="text-sm text-muted-foreground mt-1">
              {faculty.overall_rating.toFixed(1)} ({faculty.total_reviews} reviews)
            </p>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {faculty.room_no && (
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
              <MapPin className="h-4 w-4" />
              Room {faculty.room_no}
            </div>
          )}

          <div className="grid grid-cols-3 gap-4 text-sm">
            <div>
              <p className="font-medium text-card-foreground">Teaching</p>
              <p className="text-muted-foreground">{faculty.teaching_rating.toFixed(1)}</p>
            </div>
            <div>
              <p className="font-medium text-card-foreground">Marking</p>
              <p className="text-muted-foreground">{faculty.marking_rating.toFixed(1)}</p>
            </div>
            <div>
              <p className="font-medium text-card-foreground">Behavior</p>
              <p className="text-muted-foreground">{faculty.behavior_rating.toFixed(1)}</p>
            </div>
          </div>

          {showReviewButton && onReview && (
            <Button onClick={() => onReview(faculty.initial)} className="w-full mt-4" variant="outline">
              <MessageSquare className="h-4 w-4 mr-2" />
              Write Review
            </Button>
          )}
        </div>
      </CardContent>
    </Card>
  )
}
