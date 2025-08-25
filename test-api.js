const http = require("http")

// Test configuration
const BASE_URL = "http://localhost:3001"
let authToken = null

// Helper function to make HTTP requests
function makeRequest(method, path, data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const url = new URL(path, BASE_URL)
    const options = {
      method,
      headers: {
        "Content-Type": "application/json",
        ...headers,
      },
    }

    const req = http.request(url, options, (res) => {
      let body = ""
      res.on("data", (chunk) => {
        body += chunk
      })
      res.on("end", () => {
        try {
          const jsonBody = body ? JSON.parse(body) : {}
          resolve({
            status: res.statusCode,
            data: jsonBody,
            headers: res.headers,
          })
        } catch (error) {
          resolve({
            status: res.statusCode,
            data: body,
            headers: res.headers,
          })
        }
      })
    })

    req.on("error", reject)

    if (data) {
      req.write(JSON.stringify(data))
    }

    req.end()
  })
}

// Test functions
async function testLogin() {
  console.log("\n=== Testing Login ===")

  // Test admin login
  console.log("Testing admin login...")
  const adminLogin = await makeRequest("POST", "/api/auth/login", {
    id: "admin001",
    password: "admin123",
    role: "admin",
  })

  if (adminLogin.status === 200 && adminLogin.data.success) {
    authToken = JSON.stringify(adminLogin.data.user)
    console.log("✅ Admin login successful")
    console.log("User:", adminLogin.data.user)
  } else {
    console.log("❌ Admin login failed:", adminLogin.data)
  }

  // Test student login
  console.log("\nTesting student login...")
  const studentLogin = await makeRequest("POST", "/api/auth/login", {
    id: "2021001",
    password: "student123",
    role: "student",
  })

  if (studentLogin.status === 200 && studentLogin.data.success) {
    console.log("✅ Student login successful")
    console.log("User:", studentLogin.data.user)
  } else {
    console.log("❌ Student login failed:", studentLogin.data)
  }

  // Test invalid login
  console.log("\nTesting invalid login...")
  const invalidLogin = await makeRequest("POST", "/api/auth/login", {
    id: "invalid",
    password: "wrong",
    role: "admin",
  })

  if (invalidLogin.status === 401) {
    console.log("✅ Invalid login properly rejected")
  } else {
    console.log("❌ Invalid login should be rejected")
  }
}

async function testDepartments() {
  console.log("\n=== Testing Departments ===")

  if (!authToken) {
    console.log("❌ No auth token available")
    return
  }

  // Test get departments
  console.log("Testing get departments...")
  const getDepts = await makeRequest("GET", "/api/departments", null, {
    Authorization: `Bearer ${authToken}`,
  })

  if (getDepts.status === 200 && getDepts.data.success) {
    console.log("✅ Get departments successful")
    console.log("Departments count:", getDepts.data.data.length)
  } else {
    console.log("❌ Get departments failed:", getDepts.data)
  }

  // Test add department (admin only)
  console.log("\nTesting add department...")
  const addDept = await makeRequest(
    "POST",
    "/api/departments",
    {
      dept_name: "Test Department",
    },
    {
      Authorization: `Bearer ${authToken}`,
    },
  )

  if (addDept.status === 200 && addDept.data.success) {
    console.log("✅ Add department successful")
  } else {
    console.log("❌ Add department failed:", addDept.data)
  }
}

async function testFaculty() {
  console.log("\n=== Testing Faculty ===")

  if (!authToken) {
    console.log("❌ No auth token available")
    return
  }

  // Test get faculty
  console.log("Testing get faculty...")
  const getFaculty = await makeRequest("GET", "/api/faculty", null, {
    Authorization: `Bearer ${authToken}`,
  })

  if (getFaculty.status === 200 && getFaculty.data.success) {
    console.log("✅ Get faculty successful")
    console.log("Faculty count:", getFaculty.data.data.length)
  } else {
    console.log("❌ Get faculty failed:", getFaculty.data)
  }

  // Test get faculty with search
  console.log("\nTesting faculty search...")
  const searchFaculty = await makeRequest("GET", "/api/faculty?search=John", null, {
    Authorization: `Bearer ${authToken}`,
  })

  if (searchFaculty.status === 200) {
    console.log("✅ Faculty search successful")
  } else {
    console.log("❌ Faculty search failed:", searchFaculty.data)
  }
}

async function testCourses() {
  console.log("\n=== Testing Courses ===")

  if (!authToken) {
    console.log("❌ No auth token available")
    return
  }

  // Test get courses
  console.log("Testing get courses...")
  const getCourses = await makeRequest("GET", "/api/courses", null, {
    Authorization: `Bearer ${authToken}`,
  })

  if (getCourses.status === 200 && getCourses.data.success) {
    console.log("✅ Get courses successful")
    console.log("Courses count:", getCourses.data.data.length)
  } else {
    console.log("❌ Get courses failed:", getCourses.data)
  }
}

async function testDashboardStats() {
  console.log("\n=== Testing Dashboard Stats ===")

  if (!authToken) {
    console.log("❌ No auth token available")
    return
  }

  // Test dashboard stats
  console.log("Testing dashboard stats...")
  const getStats = await makeRequest("GET", "/api/dashboard/stats", null, {
    Authorization: `Bearer ${authToken}`,
  })

  if (getStats.status === 200 && getStats.data.success) {
    console.log("✅ Dashboard stats successful")
    console.log("Stats:", getStats.data.data)
  } else {
    console.log("❌ Dashboard stats failed:", getStats.data)
  }
}

async function testLeaderboard() {
  console.log("\n=== Testing Leaderboard ===")

  if (!authToken) {
    console.log("❌ No auth token available")
    return
  }

  // Test leaderboard
  console.log("Testing leaderboard...")
  const getLeaderboard = await makeRequest("GET", "/api/leaderboard", null, {
    Authorization: `Bearer ${authToken}`,
  })

  if (getLeaderboard.status === 200 && getLeaderboard.data.success) {
    console.log("✅ Leaderboard successful")
    console.log("Leaderboard entries:", getLeaderboard.data.data.length)
  } else {
    console.log("❌ Leaderboard failed:", getLeaderboard.data)
  }
}

async function testUnauthorizedAccess() {
  console.log("\n=== Testing Unauthorized Access ===")

  // Test accessing protected route without token
  console.log("Testing access without token...")
  const noToken = await makeRequest("GET", "/api/departments")

  if (noToken.status === 401) {
    console.log("✅ Unauthorized access properly blocked")
  } else {
    console.log("❌ Should block unauthorized access")
  }
}

// Main test runner
async function runTests() {
  console.log("🚀 Starting API Tests...")
  console.log("Make sure the server is running on http://localhost:3001")

  try {
    // Test server availability
    console.log("\n=== Testing Server Availability ===")
    const serverTest = await makeRequest("GET", "/")
    if (serverTest.status === 200) {
      console.log("✅ Server is running")
    } else {
      console.log("❌ Server is not responding")
      return
    }

    // Run all tests
    await testLogin()
    await testUnauthorizedAccess()
    await testDepartments()
    await testFaculty()
    await testCourses()
    await testDashboardStats()
    await testLeaderboard()

    console.log("\n🎉 All tests completed!")
  } catch (error) {
    console.error("❌ Test error:", error.message)
  }
}

// Run tests if this file is executed directly
if (require.main === module) {
  runTests()
}

module.exports = { runTests, makeRequest }
