import { type NextRequest, NextResponse } from "next/server"
import { executeQuery, executeQuerySingle } from "@/lib/database"
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
    const facultyInitial = searchParams.get("facultyInitial")
    const studentId = searchParams.get("studentId")
    const pending = searchParams.get("pending") === "true"

    let query = `
      SELECT r.*, f.name as faculty_name, s.name as student_name, c.name as course_name
      FROM reviews r
      JOIN faculty f ON r.faculty_initial = f.initial
      JOIN students s ON r.student_id = s.id
      JOIN courses c ON r.course_code = c.course_code
      WHERE 1=1
    `
    const params: any[] = []

    if (facultyInitial) {
      query += " AND r.faculty_initial = ?"
      params.push(facultyInitial)
    }

    if (studentId) {
      query += " AND r.student_id = ?"
      params.push(studentId)
    }

    if (pending) {
      query += " AND r.is_approved = FALSE"
    } else if (user.role === "student") {
      // Students can only see their own reviews
      query += " AND r.student_id = ?"
      params.push(user.id)
    }

    query += " ORDER BY r.created_at DESC"

    const reviews = await executeQuery(query, params)

    return NextResponse.json({ success: true, data: reviews })
  } catch (error) {
    console.error("Reviews fetch error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  try {
    const token = request.headers.get("authorization")?.replace("Bearer ", "")
    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user || user.role !== "student") {
      return NextResponse.json({ error: "Student access required" }, { status: 403 })
    }

    const {
      course_code,
      faculty_initial,
      semester,
      comment,
      behavior_rating,
      marking_rating,
      teaching_rating,
      overall_rating,
    } = await request.json()

    if (!course_code || !faculty_initial || !semester || !behavior_rating || !marking_rating || !teaching_rating) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 })
    }

    // Check if student already reviewed this faculty for this course and semester
    const existing = await executeQuerySingle(
      "SELECT id FROM reviews WHERE student_id = ? AND course_code = ? AND semester = ?",
      [user.id, course_code, semester],
    )

    if (existing) {
      return NextResponse.json({ error: "You have already reviewed this course for this semester" }, { status: 409 })
    }

    await executeQuery(
      `INSERT INTO reviews (course_code, student_id, faculty_initial, semester, comment, 
       behavior_rating, marking_rating, teaching_rating, overall_rating) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        course_code,
        user.id,
        faculty_initial,
        semester,
        comment,
        behavior_rating,
        marking_rating,
        teaching_rating,
        overall_rating,
      ],
    )

    return NextResponse.json({ success: true, message: "Review submitted successfully" })
  } catch (error) {
    console.error("Review creation error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
