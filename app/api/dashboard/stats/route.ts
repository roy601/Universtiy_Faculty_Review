import { type NextRequest, NextResponse } from "next/server"
import { executeQuerySingle } from "@/lib/database"
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

    // Get total faculty count
    const facultyCount = await executeQuerySingle("SELECT COUNT(*) as count FROM faculty")

    // Get total reviews count
    const reviewsCount = await executeQuerySingle("SELECT COUNT(*) as count FROM reviews WHERE is_approved = TRUE")

    // Get average rating
    const avgRating = await executeQuerySingle(
      "SELECT AVG(overall_rating) as avg_rating FROM faculty WHERE total_reviews > 0",
    )

    // Get pending reviews count (admin only)
    let pendingReviews = null
    if (user.role === "admin") {
      const pending = await executeQuerySingle("SELECT COUNT(*) as count FROM reviews WHERE is_approved = FALSE")
      pendingReviews = pending?.count || 0
    }

    const stats = {
      totalFaculty: facultyCount?.count || 0,
      totalReviews: reviewsCount?.count || 0,
      averageRating: avgRating?.avg_rating || 0,
      ...(pendingReviews !== null && { pendingReviews }),
    }

    return NextResponse.json({ success: true, data: stats })
  } catch (error) {
    console.error("Stats fetch error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
