const bcrypt = require("bcryptjs")

// User interface equivalent
class User {
  constructor(id, name, email, role, deptId = null) {
    this.id = id
    this.name = name
    this.email = email
    this.role = role
    this.deptId = deptId
  }
}

// Password verification function
async function verifyPassword(password, storedPassword) {
  // Check if stored password is a bcrypt hash (starts with $2b$)
  if (storedPassword.startsWith("$2b$")) {
    return await bcrypt.compare(password, storedPassword)
  }
  // Fallback to plain text comparison for development
  return password === storedPassword
}

// Hash password function
async function hashPassword(password) {
  const saltRounds = 10
  return await bcrypt.hash(password, saltRounds)
}

// Simple token verification for basic auth (expects user data as token)
function verifyToken(token) {
  try {
    // For basic auth, we expect the token to be JSON user data
    const userData = JSON.parse(token)

    // Basic validation of user data structure
    if (userData && userData.id && userData.role && userData.name && userData.email) {
      return new User(userData.id, userData.name, userData.email, userData.role, userData.deptId)
    }
    return null
  } catch (error) {
    return null
  }
}

// Generate user token (simple JSON serialization)
function generateToken(user) {
  return JSON.stringify({
    id: user.id,
    name: user.name,
    email: user.email,
    role: user.role,
    deptId: user.deptId,
  })
}

// Authentication middleware
function authenticateToken(req, res, next) {
  const authHeader = req.headers["authorization"] || req.headers["x-user-data"]
  const token = authHeader && authHeader.replace("Bearer ", "")

  if (!token) {
    return res.status(401).json({ error: "Unauthorized" })
  }

  const user = verifyToken(token)
  if (!user) {
    return res.status(401).json({ error: "Invalid token" })
  }

  req.user = user
  next()
}

// Admin-only middleware
function requireAdmin(req, res, next) {
  if (req.user.role !== "admin") {
    return res.status(403).json({ error: "Admin access required" })
  }
  next()
}

// Student-only middleware
function requireStudent(req, res, next) {
  if (req.user.role !== "student") {
    return res.status(403).json({ error: "Student access required" })
  }
  next()
}

// Auth response helper
function createAuthResponse(success, user = null, message = null) {
  return {
    success,
    user,
    message,
  }
}

module.exports = {
  User,
  verifyPassword,
  hashPassword,
  verifyToken,
  generateToken,
  authenticateToken,
  requireAdmin,
  requireStudent,
  createAuthResponse,
}
