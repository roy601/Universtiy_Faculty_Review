import { type NextRequest, NextResponse } from "next/server"
import { executeQuery, executeQuerySingle } from "@/lib/database"
import { verifyToken } from "@/lib/auth"

export async function PUT(request: NextRequest, { params }: { params: { id: string } }) {
  try {
    const token = request.headers.get("authorization")?.replace("Bearer ", "")
    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user || user.role !== "admin") {
      return NextResponse.json({ error: "Admin access required" }, { status: 403 })
    }

    // Get the review to update faculty ratings
    const review = await executeQuerySingle("SELECT * FROM reviews WHERE id = ?", [params.id])

    if (!review) {
      return NextResponse.json({ error: "Review not found" }, { status: 404 })
    }

    // Approve the review
    await executeQuery("UPDATE reviews SET is_approved = TRUE, approved_at = NOW() WHERE id = ?", [params.id])

    // Update faculty ratings using the stored procedure
    await executeQuery("CALL UpdateFacultyRatings(?)", [review.faculty_initial])

    return NextResponse.json({ success: true, message: "Review approved successfully" })
  } catch (error) {
    console.error("Review approval error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
