const express = require("express")
const cors = require("cors")
const db = require("./models/db")
const { verifyPassword, authenticateToken, requireAdmin, requireStudent, createAuthResponse } = require("./utils/auth")
require("dotenv").config()

const app = express()
const PORT = process.env.PORT || 3001

// Middleware
app.use(cors())
app.use(express.json())
app.use(express.static("public"))

app.post("/api/auth/login", (req, res) => {
  const { id, password, role } = req.body

  if (!id || !password || !role) {
    return res.status(400).json(createAuthResponse(false, null, "Missing required fields"))
  }

  let query = ""
  let userData = null

  if (role === "student") {
    query = "SELECT s.*, d.dept_name FROM students s LEFT JOIN departments d ON s.dept_id = d.id WHERE s.id = ?"
  } else if (role === "admin") {
    query = "SELECT * FROM admins WHERE a_id = ?"
  } else {
    return res.status(400).json(createAuthResponse(false, null, "Invalid role"))
  }

  db.query(query, [id], async (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json(createAuthResponse(false, null, "Internal server error"))
    }

    if (rows.length === 0) {
      return res.status(401).json(createAuthResponse(false, null, "Invalid credentials"))
    }

    const user = rows[0]
    const isValidPassword = await verifyPassword(password, user.password_hash)

    if (!isValidPassword) {
      return res.status(401).json(createAuthResponse(false, null, "Invalid credentials"))
    }

    if (role === "student") {
      userData = {
        id: user.id,
        name: user.name,
        email: user.email,
        role: "student",
        deptId: user.dept_id,
      }
    } else if (role === "admin") {
      userData = {
        id: user.a_id,
        name: user.name,
        email: user.email,
        role: "admin",
      }
    }

    res.json(createAuthResponse(true, userData))
  })
})

// Get all users endpoint
app.get("/api/users", (req, res) => {
  db.query("SELECT id, name, email, role, dept_id FROM users", (err, rows) => {
    if (err) {
      res.status(500).send(err)
      return
    }
    res.json(rows)
  })
})

// Departments endpoints
app.get("/api/departments", authenticateToken, (req, res) => {
  db.query("SELECT * FROM departments ORDER BY dept_name ASC", (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, data: rows })
  })
})

app.post("/api/departments", authenticateToken, requireAdmin, (req, res) => {
  const { dept_name } = req.body

  if (!dept_name) {
    return res.status(400).json({ error: "Department name is required" })
  }

  db.query("INSERT INTO departments (dept_name) VALUES (?)", [dept_name], (err, result) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, message: "Department added successfully" })
  })
})

// Faculty endpoints
app.get("/api/faculty", authenticateToken, (req, res) => {
  const { deptId, search } = req.query

  let query = `
    SELECT f.*, d.dept_name 
    FROM faculty f 
    LEFT JOIN departments d ON f.dept_id = d.id 
    WHERE 1=1
  `
  const params = []

  if (deptId) {
    query += " AND f.dept_id = ?"
    params.push(deptId)
  }

  if (search) {
    query += " AND (f.name LIKE ? OR f.email LIKE ?)"
    params.push(`%${search}%`, `%${search}%`)
  }

  query += " ORDER BY f.overall_rating DESC, f.name ASC"

  db.query(query, params, (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, data: rows })
  })
})

app.post("/api/faculty", authenticateToken, requireAdmin, (req, res) => {
  const { initial, name, email, room_no, specific_history, dept_id } = req.body

  if (!initial || !name || !email) {
    return res.status(400).json({ error: "Missing required fields" })
  }

  // Check if faculty already exists
  db.query("SELECT initial FROM faculty WHERE initial = ? OR email = ?", [initial, email], (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }

    if (rows.length > 0) {
      return res.status(409).json({ error: "Faculty with this initial or email already exists" })
    }

    db.query(
      `INSERT INTO faculty (initial, name, email, room_no, specific_history, dept_id, a_id) 
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [initial, name, email, room_no, specific_history, dept_id, req.user.id],
      (err, result) => {
        if (err) {
          console.error("Database error:", err)
          return res.status(500).json({ error: "Internal server error" })
        }
        res.json({ success: true, message: "Faculty added successfully" })
      },
    )
  })
})

// Faculty by initial
app.get("/api/faculty/:initial", authenticateToken, (req, res) => {
  const query = `
    SELECT f.*, d.dept_name 
    FROM faculty f 
    LEFT JOIN departments d ON f.dept_id = d.id 
    WHERE f.initial = ?
  `

  db.query(query, [req.params.initial], (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }

    if (rows.length === 0) {
      return res.status(404).json({ error: "Faculty not found" })
    }

    res.json({ success: true, data: rows[0] })
  })
})

app.put("/api/faculty/:initial", authenticateToken, requireAdmin, (req, res) => {
  const { name, email, room_no, specific_history, dept_id } = req.body

  db.query(
    `UPDATE faculty 
     SET name = ?, email = ?, room_no = ?, specific_history = ?, dept_id = ?
     WHERE initial = ?`,
    [name, email, room_no, specific_history, dept_id, req.params.initial],
    (err, result) => {
      if (err) {
        console.error("Database error:", err)
        return res.status(500).json({ error: "Internal server error" })
      }
      res.json({ success: true, message: "Faculty updated successfully" })
    },
  )
})

app.delete("/api/faculty/:initial", authenticateToken, requireAdmin, (req, res) => {
  db.query("DELETE FROM faculty WHERE initial = ?", [req.params.initial], (err, result) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, message: "Faculty deleted successfully" })
  })
})

// Courses endpoints
app.get("/api/courses", authenticateToken, (req, res) => {
  const { facultyInitial } = req.query

  let query = `
    SELECT c.*, f.name as faculty_name, d.dept_name
    FROM courses c
    LEFT JOIN faculty f ON c.faculty_initial = f.initial
    LEFT JOIN departments d ON c.dept_id = d.id
    WHERE 1=1
  `
  const params = []

  if (facultyInitial) {
    query += " AND c.faculty_initial = ?"
    params.push(facultyInitial)
  }

  query += " ORDER BY c.course_code ASC"

  db.query(query, params, (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, data: rows })
  })
})

app.post("/api/courses", authenticateToken, requireAdmin, (req, res) => {
  const { course_code, name, materials, faculty_initial, dept_id } = req.body

  if (!course_code || !name) {
    return res.status(400).json({ error: "Missing required fields" })
  }

  db.query(
    `INSERT INTO courses (course_code, name, materials, faculty_initial, dept_id, a_id) 
     VALUES (?, ?, ?, ?, ?, ?)`,
    [course_code, name, materials, faculty_initial, dept_id, req.user.id],
    (err, result) => {
      if (err) {
        console.error("Database error:", err)
        return res.status(500).json({ error: "Internal server error" })
      }
      res.json({ success: true, message: "Course added successfully" })
    },
  )
})

// Reviews endpoints
app.get("/api/reviews", authenticateToken, (req, res) => {
  const { facultyInitial, studentId, pending } = req.query

  let query = `
    SELECT r.*, f.name as faculty_name, s.name as student_name, c.name as course_name
    FROM reviews r
    JOIN faculty f ON r.faculty_initial = f.initial
    JOIN students s ON r.student_id = s.id
    JOIN courses c ON r.course_code = c.course_code
    WHERE 1=1
  `
  const params = []

  if (facultyInitial) {
    query += " AND r.faculty_initial = ?"
    params.push(facultyInitial)
  }

  if (studentId) {
    query += " AND r.student_id = ?"
    params.push(studentId)
  }

  if (pending === "true") {
    query += " AND r.is_approved = FALSE"
  } else if (req.user.role === "student") {
    query += " AND r.student_id = ?"
    params.push(req.user.id)
  }

  query += " ORDER BY r.created_at DESC"

  db.query(query, params, (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, data: rows })
  })
})

app.post("/api/reviews", authenticateToken, requireStudent, (req, res) => {
  const {
    course_code,
    faculty_initial,
    semester,
    comment,
    behavior_rating,
    marking_rating,
    teaching_rating,
    overall_rating,
  } = req.body

  if (!course_code || !faculty_initial || !semester || !behavior_rating || !marking_rating || !teaching_rating) {
    return res.status(400).json({ error: "Missing required fields" })
  }

  // Check if student already reviewed this faculty for this course and semester
  db.query(
    "SELECT id FROM reviews WHERE student_id = ? AND course_code = ? AND semester = ?",
    [req.user.id, course_code, semester],
    (err, rows) => {
      if (err) {
        console.error("Database error:", err)
        return res.status(500).json({ error: "Internal server error" })
      }

      if (rows.length > 0) {
        return res.status(409).json({ error: "You have already reviewed this course for this semester" })
      }

      db.query(
        `INSERT INTO reviews (course_code, student_id, faculty_initial, semester, comment, 
         behavior_rating, marking_rating, teaching_rating, overall_rating) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          course_code,
          req.user.id,
          faculty_initial,
          semester,
          comment,
          behavior_rating,
          marking_rating,
          teaching_rating,
          overall_rating,
        ],
        (err, result) => {
          if (err) {
            console.error("Database error:", err)
            return res.status(500).json({ error: "Internal server error" })
          }
          res.json({ success: true, message: "Review submitted successfully" })
        },
      )
    },
  )
})

// Review approval
app.put("/api/reviews/:id/approve", authenticateToken, requireAdmin, (req, res) => {
  // Get the review to update faculty ratings
  db.query("SELECT * FROM reviews WHERE id = ?", [req.params.id], (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }

    if (rows.length === 0) {
      return res.status(404).json({ error: "Review not found" })
    }

    const review = rows[0]

    // Approve the review
    db.query("UPDATE reviews SET is_approved = TRUE, approved_at = NOW() WHERE id = ?", [req.params.id], (err) => {
      if (err) {
        console.error("Database error:", err)
        return res.status(500).json({ error: "Internal server error" })
      }

      // Update faculty ratings using the stored procedure
      db.query("CALL UpdateFacultyRatings(?)", [review.faculty_initial], (err) => {
        if (err) {
          console.error("Database error:", err)
          return res.status(500).json({ error: "Internal server error" })
        }
        res.json({ success: true, message: "Review approved successfully" })
      })
    })
  })
})

// Dashboard stats endpoint
app.get("/api/dashboard/stats", authenticateToken, (req, res) => {
  // Get total faculty count
  db.query("SELECT COUNT(*) as count FROM faculty", (err, facultyResult) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }

    // Get total reviews count
    db.query("SELECT COUNT(*) as count FROM reviews WHERE is_approved = TRUE", (err, reviewsResult) => {
      if (err) {
        console.error("Database error:", err)
        return res.status(500).json({ error: "Internal server error" })
      }

      // Get average rating
      db.query("SELECT AVG(overall_rating) as avg_rating FROM faculty WHERE total_reviews > 0", (err, avgResult) => {
        if (err) {
          console.error("Database error:", err)
          return res.status(500).json({ error: "Internal server error" })
        }

        const stats = {
          totalFaculty: facultyResult[0].count || 0,
          totalReviews: reviewsResult[0].count || 0,
          averageRating: avgResult[0].avg_rating || 0,
        }

        // Get pending reviews count for admin
        if (req.user.role === "admin") {
          db.query("SELECT COUNT(*) as count FROM reviews WHERE is_approved = FALSE", (err, pendingResult) => {
            if (err) {
              console.error("Database error:", err)
              return res.status(500).json({ error: "Internal server error" })
            }
            stats.pendingReviews = pendingResult[0].count || 0
            res.json({ success: true, data: stats })
          })
        } else {
          res.json({ success: true, data: stats })
        }
      })
    })
  })
})

// Leaderboard endpoint
app.get("/api/leaderboard", authenticateToken, (req, res) => {
  const { category = "overall", limit = "10" } = req.query

  let orderBy = "overall_rating"
  switch (category) {
    case "teaching":
      orderBy = "teaching_rating"
      break
    case "marking":
      orderBy = "marking_rating"
      break
    case "behavior":
      orderBy = "behavior_rating"
      break
    default:
      orderBy = "overall_rating"
  }

  const query = `
    SELECT f.*, d.dept_name, 
    RANK() OVER (ORDER BY f.${orderBy} DESC, f.total_reviews DESC) as rank_position
    FROM faculty f
    LEFT JOIN departments d ON f.dept_id = d.id
    WHERE f.total_reviews > 0
    ORDER BY f.${orderBy} DESC, f.total_reviews DESC
    LIMIT ?
  `

  db.query(query, [Number.parseInt(limit)], (err, rows) => {
    if (err) {
      console.error("Database error:", err)
      return res.status(500).json({ error: "Internal server error" })
    }
    res.json({ success: true, data: rows })
  })
})

// Basic HTML page for testing
app.get("/", (req, res) => {
  res.send(`
    <html>
      <head><title>Faculty Review System API</title></head>
      <body>
        <h1>Faculty Review System API</h1>
        <p>Express.js server is running on port ${PORT}</p>
        <h2>Available Endpoints:</h2>
        <ul>
          <li>POST /api/auth/login - User login</li>
          <li>GET /api/departments - Get all departments</li>
          <li>POST /api/departments - Add new department (admin)</li>
          <li>GET /api/faculty - Get all faculty</li>
          <li>POST /api/faculty - Add new faculty (admin)</li>
          <li>GET /api/faculty/:initial - Get faculty by initial</li>
          <li>PUT /api/faculty/:initial - Update faculty (admin)</li>
          <li>DELETE /api/faculty/:initial - Delete faculty (admin)</li>
          <li>GET /api/courses - Get all courses</li>
          <li>POST /api/courses - Add new course (admin)</li>
          <li>GET /api/reviews - Get reviews</li>
          <li>POST /api/reviews - Add new review (student)</li>
          <li>PUT /api/reviews/:id/approve - Approve review (admin)</li>
          <li>GET /api/dashboard/stats - Get dashboard statistics</li>
          <li>GET /api/leaderboard - Get faculty leaderboard</li>
        </ul>
      </body>
    </html>
  `)
})

// Start the server
app.listen(PORT, () => {
  console.log(`Server is running on http://localhost:${PORT}`)
})
