"use client"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Users, MessageSquare, Star, TrendingUp } from "lucide-react"

interface StatsCardsProps {
  stats: {
    totalFaculty: number
    totalReviews: number
    averageRating: number
    pendingReviews?: number
  }
}

export function StatsCards({ stats }: StatsCardsProps) {
  const cards = [
    {
      title: "Total Faculty",
      value: stats.totalFaculty,
      icon: Users,
      color: "text-chart-1",
    },
    {
      title: "Total Reviews",
      value: stats.totalReviews,
      icon: MessageSquare,
      color: "text-chart-2",
    },
    {
      title: "Average Rating",
      value: stats.averageRating.toFixed(1),
      icon: Star,
      color: "text-accent",
    },
    ...(stats.pendingReviews !== undefined
      ? [
          {
            title: "Pending Reviews",
            value: stats.pendingReviews,
            icon: TrendingUp,
            color: "text-muted-foreground",
          },
        ]
      : []),
  ]

  return (
    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      {cards.map((card) => (
        <Card key={card.title}>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">{card.title}</CardTitle>
            <card.icon className={`h-4 w-4 ${card.color}`} />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-card-foreground">{card.value}</div>
          </CardContent>
        </Card>
      ))}
    </div>
  )
}
