import { type NextRequest, NextResponse } from "next/server"
import { executeQuery, executeQuerySingle } from "@/lib/database"
import { verifyToken } from "@/lib/auth"

export async function GET(request: NextRequest, { params }: { params: { initial: string } }) {
  try {
    const token = request.headers.get("authorization")?.replace("Bearer ", "")
    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user) {
      return NextResponse.json({ error: "Invalid token" }, { status: 401 })
    }

    const faculty = await executeQuerySingle(
      `SELECT f.*, d.dept_name 
       FROM faculty f 
       LEFT JOIN departments d ON f.dept_id = d.id 
       WHERE f.initial = ?`,
      [params.initial],
    )

    if (!faculty) {
      return NextResponse.json({ error: "Faculty not found" }, { status: 404 })
    }

    return NextResponse.json({ success: true, data: faculty })
  } catch (error) {
    console.error("Faculty fetch error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}

export async function PUT(request: NextRequest, { params }: { params: { initial: string } }) {
  try {
    const token = request.headers.get("authorization")?.replace("Bearer ", "")
    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user || user.role !== "admin") {
      return NextResponse.json({ error: "Admin access required" }, { status: 403 })
    }

    const { name, email, room_no, specific_history, dept_id } = await request.json()

    await executeQuery(
      `UPDATE faculty 
       SET name = ?, email = ?, room_no = ?, specific_history = ?, dept_id = ?
       WHERE initial = ?`,
      [name, email, room_no, specific_history, dept_id, params.initial],
    )

    return NextResponse.json({ success: true, message: "Faculty updated successfully" })
  } catch (error) {
    console.error("Faculty update error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}

export async function DELETE(request: NextRequest, { params }: { params: { initial: string } }) {
  try {
    const token = request.headers.get("authorization")?.replace("Bearer ", "")
    if (!token) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const user = verifyToken(token)
    if (!user || user.role !== "admin") {
      return NextResponse.json({ error: "Admin access required" }, { status: 403 })
    }

    await executeQuery("DELETE FROM faculty WHERE initial = ?", [params.initial])

    return NextResponse.json({ success: true, message: "Faculty deleted successfully" })
  } catch (error) {
    console.error("Faculty deletion error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
