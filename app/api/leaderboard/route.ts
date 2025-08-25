import { type NextRequest, NextResponse } from "next/server"
import { executeQuery } from "@/lib/database"
import { verifyToken } from "@/lib/auth"

export async function GET(request: NextRequest) {
  try {
    const token = request.headers.get("authorization")?.replace("Bearer ", "")
    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user) {
      return NextResponse.json({ error: "Invalid token" }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const category = searchParams.get("category") || "overall"
    const limit = Number.parseInt(searchParams.get("limit") || "10")

    let orderBy = "overall_rating"
    switch (category) {
      case "teaching":
        orderBy = "teaching_rating"
        break
      case "marking":
        orderBy = "marking_rating"
        break
      case "behavior":
        orderBy = "behavior_rating"
        break
      default:
        orderBy = "overall_rating"
    }

    const leaderboard = await executeQuery(
      `SELECT f.*, d.dept_name, 
       RANK() OVER (ORDER BY f.${orderBy} DESC, f.total_reviews DESC) as rank_position
       FROM faculty f
       LEFT JOIN departments d ON f.dept_id = d.id
       WHERE f.total_reviews > 0
       ORDER BY f.${orderBy} DESC, f.total_reviews DESC
       LIMIT ?`,
      [limit],
    )

    return NextResponse.json({ success: true, data: leaderboard })
  } catch (error) {
    console.error("Leaderboard fetch error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
