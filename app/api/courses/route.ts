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
    const facultyInitial = searchParams.get("facultyInitial")

    let query = `
      SELECT c.*, f.name as faculty_name, d.dept_name
      FROM courses c
      LEFT JOIN faculty f ON c.faculty_initial = f.initial
      LEFT JOIN departments d ON c.dept_id = d.id
      WHERE 1=1
    `
    const params: any[] = []

    if (facultyInitial) {
      query += " AND c.faculty_initial = ?"
      params.push(facultyInitial)
    }

    query += " ORDER BY c.course_code ASC"

    const courses = await executeQuery(query, params)

    return NextResponse.json({ success: true, data: courses })
  } catch (error) {
    console.error("Courses fetch error:", error)
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
    if (!user || user.role !== "admin") {
      return NextResponse.json({ error: "Admin access required" }, { status: 403 })
    }

    const { course_code, name, materials, faculty_initial, dept_id } = await request.json()

    if (!course_code || !name) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 })
    }

    await executeQuery(
      `INSERT INTO courses (course_code, name, materials, faculty_initial, dept_id, a_id) 
       VALUES (?, ?, ?, ?, ?, ?)`,
      [course_code, name, materials, faculty_initial, dept_id, user.id],
    )

    return NextResponse.json({ success: true, message: "Course added successfully" })
  } catch (error) {
    console.error("Course creation error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
