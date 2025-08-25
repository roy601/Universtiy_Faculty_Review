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

    const departments = await executeQuery("SELECT * FROM departments ORDER BY dept_name ASC")

    return NextResponse.json({ success: true, data: departments })
  } catch (error) {
    console.error("Departments fetch error:", error)
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

    const { dept_name } = await request.json()

    if (!dept_name) {
      return NextResponse.json({ error: "Department name is required" }, { status: 400 })
    }

    await executeQuery("INSERT INTO departments (dept_name) VALUES (?)", [dept_name])

    return NextResponse.json({ success: true, message: "Department added successfully" })
  } catch (error) {
    console.error("Department creation error:", error)
    return NextResponse.json({ error: "Internal server error" }, { status: 500 })
  }
}
