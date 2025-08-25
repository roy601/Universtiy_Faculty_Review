"use client"

import type React from "react"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Star } from "lucide-react"

interface ReviewFormProps {
  facultyInitial: string
  facultyName: string
  onSubmit: (reviewData: any) => void
  onCancel: () => void
  courses: Array<{ course_code: string; name: string }>
}

export function ReviewForm({ facultyInitial, facultyName, onSubmit, onCancel, courses }: ReviewFormProps) {
  const [formData, setFormData] = useState({
    course_code: "",
    semester: "",
    comment: "",
    behavior_rating: 0,
    marking_rating: 0,
    teaching_rating: 0,
  })

  const [hoveredRating, setHoveredRating] = useState<{ [key: string]: number }>({})

  const handleRatingClick = (category: string, rating: number) => {
    setFormData((prev) => ({
      ...prev,
      [`${category}_rating`]: rating,
    }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const overall_rating = (formData.behavior_rating + formData.marking_rating + formData.teaching_rating) / 3
    onSubmit({
      ...formData,
      faculty_initial: facultyInitial,
      overall_rating,
    })
  }

  const renderStarRating = (category: string, currentRating: number) => {
    const hovered = hoveredRating[category] || 0
    const displayRating = hovered || currentRating

    return (
      <div className="flex gap-1">
        {Array.from({ length: 5 }, (_, i) => (
          <Star
            key={i}
            className={`h-6 w-6 cursor-pointer transition-colors ${
              i < displayRating ? "fill-accent text-accent" : "text-muted-foreground hover:text-accent"
            }`}
            onClick={() => handleRatingClick(category, i + 1)}
            onMouseEnter={() => setHoveredRating((prev) => ({ ...prev, [category]: i + 1 }))}
            onMouseLeave={() => setHoveredRating((prev) => ({ ...prev, [category]: 0 }))}
          />
        ))}
      </div>
    )
  }

  return (
    <Card className="max-w-2xl mx-auto">
      <CardHeader>
        <CardTitle>Review {facultyName}</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="course">Course</Label>
              <Select
                value={formData.course_code}
                onValueChange={(value) => setFormData((prev) => ({ ...prev, course_code: value }))}
                required
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select a course" />
                </SelectTrigger>
                <SelectContent>
                  {courses.map((course) => (
                    <SelectItem key={course.course_code} value={course.course_code}>
                      {course.course_code} - {course.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label htmlFor="semester">Semester</Label>
              <Input
                id="semester"
                placeholder="e.g., Fall 2024"
                value={formData.semester}
                onChange={(e) => setFormData((prev) => ({ ...prev, semester: e.target.value }))}
                required
              />
            </div>
          </div>

          <div className="space-y-4">
            <div className="space-y-2">
              <Label>Teaching Quality</Label>
              {renderStarRating("teaching", formData.teaching_rating)}
            </div>

            <div className="space-y-2">
              <Label>Marking Fairness</Label>
              {renderStarRating("marking", formData.marking_rating)}
            </div>

            <div className="space-y-2">
              <Label>Behavior & Attitude</Label>
              {renderStarRating("behavior", formData.behavior_rating)}
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="comment">Comments (Optional)</Label>
            <Textarea
              id="comment"
              placeholder="Share your experience with this faculty member..."
              value={formData.comment}
              onChange={(e) => setFormData((prev) => ({ ...prev, comment: e.target.value }))}
              rows={4}
            />
          </div>

          <div className="flex gap-4">
            <Button type="submit" className="flex-1">
              Submit Review
            </Button>
            <Button type="button" variant="outline" onClick={onCancel}>
              Cancel
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  )
}
