import { type NextRequest, NextResponse } from "next/server"
import { executeQuerySingle } from "@/lib/database"
import { verifyPassword, type User } from "@/lib/auth"

export async function POST(request: NextRequest) {
  try {
    const { id, password, role } = await request.json()

    if (!id || !password || !role) {
      return NextResponse.json({ success: false, message: "Missing required fields" }, { status: 400 })
    }

    let user: any = null
    let userData: User | null = null

    if (role === "student") {
      // Query student table
      user = await executeQuerySingle(
        "SELECT s.*, d.dept_name FROM students s LEFT JOIN departments d ON s.dept_id = d.id WHERE s.id = ?",
        [id],
      )

      if (user && (await verifyPassword(password, user.password_hash))) {
        userData = {
          id: user.id,
          name: user.name,
          email: user.email,
          role: "student",
          deptId: user.dept_id,
        }
      }
    } else if (role === "admin") {
      // Query admin table
      user = await executeQuerySingle("SELECT * FROM admins WHERE a_id = ?", [id])

      if (user && (await verifyPassword(password, user.password_hash))) {
        userData = {
          id: user.a_id,
          name: user.name,
          email: user.email,
          role: "admin",
        }
      }
    }

    if (!userData) {
      return NextResponse.json({ success: false, message: "Invalid credentials" }, { status: 401 })
    }

    return NextResponse.json({
      success: true,
      user: userData,
    })
  } catch (error) {
    console.error("Login error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}
