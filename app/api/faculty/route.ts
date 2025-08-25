import { type NextRequest, NextResponse } from "next/server"
import { executeQuery, executeQuerySingle } from "@/lib/database"
import { verifyToken } from "@/lib/auth"

export async function GET(request: NextRequest) {
  try {
    const authHeader = request.headers.get("authorization")
    const token = authHeader?.replace("Bearer ", "") || request.headers.get("x-user-data")

    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user) {
      return NextResponse.json({ error: "Invalid authentication" }, { status: 401 })
    }

    const searchParams = request.nextUrl.searchParams
    const deptId = searchParams.get("deptId")
    const search = searchParams.get("search")

    let query = `
      SELECT f.*, d.dept_name 
      FROM faculty f 
      LEFT JOIN departments d ON f.dept_id = d.id 
      WHERE 1=1
    `
    const params: any[] = []

    if (deptId) {
      query += " AND f.dept_id = ?"
      params.push(deptId)
    }

    if (search) {
      query += " AND (f.name LIKE ? OR f.email LIKE ?)"
      params.push(`%${search}%`, `%${search}%`)
    }

    query += " ORDER BY f.overall_rating DESC, f.name ASC"

    const faculty = await executeQuery(query, params)

    return NextResponse.json({ success: true, data: faculty })
  } catch (error) {
    console.error("Faculty fetch error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  try {
    const authHeader = request.headers.get("authorization")
    const token = authHeader?.replace("Bearer ", "") || request.headers.get("x-user-data")

    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user || user.role !== "admin") {
      return NextResponse.json({ error: "Admin access required" }, { status: 403 })
    }

    const { initial, name, email, room_no, specific_history, dept_id } = await request.json()

    if (!initial || !name || !email) {
      return NextResponse.json({ error: "Missing required fields" }, { status: 400 })
    }

    // Check if faculty already exists
    const existing = await executeQuerySingle("SELECT initial FROM faculty WHERE initial = ? OR email = ?", [
      initial,
      email,
    ])

    if (existing) {
      return NextResponse.json({ error: "Faculty with this initial or email already exists" }, { status: 409 })
    }

    await executeQuery(
      `INSERT INTO faculty (initial, name, email, room_no, specific_history, dept_id, a_id) 
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [initial, name, email, room_no, specific_history, dept_id, user.id],
    )

    return NextResponse.json({ success: true, message: "Faculty added successfully" })
  } catch (error) {
    console.error("Faculty creation error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
