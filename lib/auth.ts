import bcrypt from "bcryptjs"

export interface User {
  id: string
  name: string
  email: string
  role: "student" | "admin"
  deptId?: number
}

export interface AuthResponse {
  success: boolean
  user?: User
  message?: string
}

// Hash password
export async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, 12)
}

// Verify password
export async function verifyPassword(password: string, hashedPassword: string): Promise<boolean> {
  return bcrypt.compare(password, hashedPassword)
}

// Simple token verification for basic auth (expects user data as token)
export function verifyToken(token: string): User | null {
  try {
    // For basic auth, we expect the token to be JSON user data
    const userData = JSON.parse(token)

    // Basic validation of user data structure
    if (userData && userData.id && userData.role && userData.name && userData.email) {
      return {
        id: userData.id,
        name: userData.name,
        email: userData.email,
        role: userData.role,
        deptId: userData.deptId,
      }
    }
    return null
  } catch (error) {
    return null
  }
}
