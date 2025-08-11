// Global variables
let currentRole = "student"
let facultyData = []
let reviewsData = []
let currentEditingFaculty = null

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  initializeData()
  updateDashboard()
  updateFacultyGrid()
  updateReviewsList()
  updatePendingReviewsList()
  updateLeaderboard()
  setupReviewForm()

  // Set initial role
  changeRole()
})

// Sample data initialization
function initializeData() {
  // Initialize faculty data if not exists
  if (!localStorage.getItem("facultyData")) {
    facultyData = [
      {
        id: 1,
        name: "Dr. Sarah Johnson",
        department: "Computer Science and Engineering",
        role: "Teaching + Marking",
        email: "sarah.johnson@university.edu",
        phone: "+1-555-0101",
        office: "CSE Building, Room 301",
        bio: "Dr. Johnson specializes in artificial intelligence and machine learning with over 15 years of experience.",
        courses: ["CSE101", "CSE301", "CSE401"],
        teachingRating: 2.8,
        markingRating: 2.6,
        overallRating: 2.7,
        totalReviews: 45,
        totalVotes: 120,
      },
      {
        id: 2,
        name: "Prof. Michael Chen",
        department: "Mathematics and Natural Sciences",
        role: "Teaching only",
        email: "michael.chen@university.edu",
        phone: "+1-555-0102",
        office: "MNS Building, Room 205",
        bio: "Professor Chen is an expert in calculus and differential equations.",
        courses: ["MATH101", "MATH201", "MATH301"],
        teachingRating: 2.6,
        markingRating: 0,
        overallRating: 2.6,
        totalReviews: 38,
        totalVotes: 95,
      },
      {
        id: 3,
        name: "Dr. Emily Rodriguez",
        department: "Architecture",
        role: "Teaching + Marking",
        email: "emily.rodriguez@university.edu",
        phone: "+1-555-0103",
        office: "Architecture Building, Room 401",
        bio: "Dr. Rodriguez focuses on sustainable architecture and urban planning.",
        courses: ["ARCH101", "ARCH201", "ARCH401"],
        teachingRating: 2.9,
        markingRating: 2.8,
        overallRating: 2.85,
        totalReviews: 52,
        totalVotes: 140,
      },
      {
        id: 4,
        name: "Prof. David Wilson",
        department: "Pharmacy",
        role: "Marking only",
        email: "david.wilson@university.edu",
        phone: "+1-555-0104",
        office: "Pharmacy Building, Room 102",
        bio: "Professor Wilson specializes in pharmaceutical chemistry and drug development.",
        courses: ["PHAR101", "PHAR201", "PHAR301"],
        teachingRating: 0,
        markingRating: 2.4,
        overallRating: 2.4,
        totalReviews: 29,
        totalVotes: 78,
      },
      {
        id: 5,
        name: "Dr. Lisa Thompson",
        department: "English and Humanities",
        role: "Teaching + Marking",
        email: "lisa.thompson@university.edu",
        phone: "+1-555-0105",
        office: "Humanities Building, Room 301",
        bio: "Dr. Thompson is an expert in modern literature and creative writing.",
        courses: ["ENG101", "ENG201", "ENG401"],
        teachingRating: 2.7,
        markingRating: 2.6,
        overallRating: 2.65,
        totalReviews: 41,
        totalVotes: 110,
      },
    ]
    localStorage.setItem("facultyData", JSON.stringify(facultyData))
  } else {
    facultyData = JSON.parse(localStorage.getItem("facultyData"))
  }

  // Initialize reviews data if not exists
  if (!localStorage.getItem("reviewsData")) {
    reviewsData = [
      {
        id: 1,
        facultyId: 1,
        facultyName: "Dr. Sarah Johnson",
        course: "CSE101",
        teachingRating: 3,
        markingRating: 2,
        comment: "Excellent professor! Very clear explanations and helpful during office hours.",
        reviewerName: "John Smith",
        date: "2024-01-15",
        upvotes: 15,
        downvotes: 2,
        userVote: null,
        status: "approved",
      },
      {
        id: 2,
        facultyId: 1,
        facultyName: "Dr. Sarah Johnson",
        course: "CSE301",
        teachingRating: 2,
        markingRating: 3,
        comment: "Good course content but assignments can be challenging.",
        reviewerName: "Jane Doe",
        date: "2024-01-10",
        upvotes: 8,
        downvotes: 1,
        userVote: null,
        status: "approved",
      },
      {
        id: 3,
        facultyId: 2,
        facultyName: "Prof. Michael Chen",
        course: "MATH101",
        teachingRating: 3,
        markingRating: null,
        comment: "Makes complex math concepts easy to understand!",
        reviewerName: "Alice Johnson",
        date: "2024-01-12",
        upvotes: 12,
        downvotes: 0,
        userVote: null,
        status: "approved",
      },
      {
        id: 4,
        facultyId: 3,
        facultyName: "Dr. Emily Rodriguez",
        course: "ARCH101",
        teachingRating: 3,
        markingRating: 3,
        comment: "Amazing architecture professor. Very engaging lectures.",
        reviewerName: "Bob Wilson",
        date: "2024-01-08",
        upvotes: 18,
        downvotes: 1,
        userVote: null,
        status: "approved",
      },
    ]
    localStorage.setItem("reviewsData", JSON.stringify(reviewsData))
  } else {
    reviewsData = JSON.parse(localStorage.getItem("reviewsData"))
  }
}

// Navigation functions
function showSection(sectionName) {
  // Hide all sections
  document.querySelectorAll(".section").forEach((section) => {
    section.classList.remove("active")
  })

  // Show selected section
  document.getElementById(sectionName).classList.add("active")

  // Update navigation buttons
  document.querySelectorAll(".nav-btn").forEach((btn) => {
    btn.classList.remove("active")
  })
  event.target.classList.add("active")

  // Update content based on section
  switch (sectionName) {
    case "dashboard":
      updateDashboard()
      break
    case "faculty":
      updateFacultyGrid()
      break
    case "reviews":
      updateReviewsList()
      populateReviewFilters()
      break
    case "pending-reviews":
      updatePendingReviewsList()
      break
    case "leaderboard":
      updateLeaderboard()
      break
  }
}

// Role management
function changeRole() {
  currentRole = document.getElementById("roleSelect").value
  document.body.className = currentRole

  // Update UI based on role
  if (currentRole === "admin") {
    document.body.classList.add("admin")
  } else {
    document.body.classList.remove("admin")
  }
}

// Dashboard functions
function updateDashboard() {
  const totalFaculty = facultyData.length
  const approvedReviews = reviewsData.filter((r) => r.status === "approved").length
  const pendingReviews = reviewsData.filter((r) => r.status === "pending").length
  const totalCourses = [...new Set(facultyData.flatMap((f) => f.courses))].length
  const totalVotes = reviewsData
    .filter((r) => r.status === "approved")
    .reduce((sum, review) => sum + review.upvotes + review.downvotes, 0)

  document.getElementById("totalFaculty").textContent = totalFaculty
  document.getElementById("totalReviews").textContent = approvedReviews
  if (document.getElementById("pendingReviews")) {
    document.getElementById("pendingReviews").textContent = pendingReviews
  }
  document.getElementById("totalCourses").textContent = totalCourses
  document.getElementById("totalVotes").textContent = totalVotes

  updateRecentActivity()
}

function updateRecentActivity() {
  const recentActivity = document.getElementById("recentActivity")
  const activities = []

  // Get recent approved reviews
  const recentReviews = reviewsData
    .filter((r) => r.status === "approved")
    .sort((a, b) => new Date(b.date) - new Date(a.date))
    .slice(0, 5)

  recentReviews.forEach((review) => {
    activities.push({
      type: "review",
      text: `${review.reviewerName} reviewed ${review.facultyName} for ${review.course}`,
      date: review.date,
      icon: "fas fa-star",
    })
  })

  // Sort by date
  activities.sort((a, b) => new Date(b.date) - new Date(a.date))

  recentActivity.innerHTML = activities
    .map(
      (activity) => `
        <div class="activity-item">
            <i class="${activity.icon}"></i>
            ${activity.text}
            <small style="display: block; margin-top: 0.5rem; color: #666;">
                ${formatDate(activity.date)}
            </small>
        </div>
    `,
    )
    .join("")
}

// Faculty management functions
function updateFacultyGrid() {
  const facultyGrid = document.getElementById("facultyGrid")
  const filteredFaculty = getFilteredFaculty()

  facultyGrid.innerHTML = filteredFaculty
    .map(
      (faculty) => `
        <div class="faculty-card">
            <div class="faculty-header">
                <div class="faculty-avatar">
                    ${faculty.name
                      .split(" ")
                      .map((n) => n[0])
                      .join("")}
                </div>
                <h3>${faculty.name}</h3>
                <p>${faculty.department}</p>
            </div>
            <div class="faculty-body">
                <div class="faculty-info">
                    <p><i class="fas fa-user-tie"></i> ${faculty.role}</p>
                    <p><i class="fas fa-envelope"></i> ${faculty.email}</p>
                    <p><i class="fas fa-phone"></i> ${faculty.phone || "N/A"}</p>
                    <p><i class="fas fa-map-marker-alt"></i> ${faculty.office || "N/A"}</p>
                </div>
                <div class="faculty-rating">
                    <div class="stars">${generateRatingDisplay(faculty)}</div>
                </div>
                <div class="faculty-courses">
                    ${faculty.courses.map((course) => `<span class="course-tag">${course}</span>`).join("")}
                </div>
                <p style="margin-bottom: 1rem; color: #666; font-size: 0.9rem;">${faculty.bio}</p>
                <div class="faculty-actions">
                    ${
                      currentRole === "admin"
                        ? `
                        <button class="btn-secondary btn-small" onclick="editFaculty(${faculty.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-secondary btn-small" onclick="deleteFaculty(${faculty.id})" style="background-color: #e74c3c;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    `
                        : ""
                    }
                    <button class="btn-primary btn-small" onclick="viewFacultyReviews(${faculty.id})">
                        <i class="fas fa-star"></i> Reviews
                    </button>
                </div>
            </div>
        </div>
    `,
    )
    .join("")
}

function getFilteredFaculty() {
  const searchTerm = document.getElementById("facultySearch").value.toLowerCase()
  const departmentFilter = document.getElementById("departmentFilter").value
  const ratingFilter = document.getElementById("ratingFilter").value
  const roleFilter = document.getElementById("roleFilter").value

  return facultyData.filter((faculty) => {
    const matchesSearch =
      faculty.name.toLowerCase().includes(searchTerm) || faculty.department.toLowerCase().includes(searchTerm)
    const matchesDepartment = !departmentFilter || faculty.department === departmentFilter
    const matchesRating = !ratingFilter || faculty.overallRating >= Number.parseFloat(ratingFilter)
    const matchesRole = !roleFilter || faculty.role === roleFilter

    return matchesSearch && matchesDepartment && matchesRating && matchesRole
  })
}

function filterFaculty() {
  updateFacultyGrid()
}

// Faculty modal functions
function openFacultyModal(facultyId = null) {
  const modal = document.getElementById("facultyModal")
  const form = document.getElementById("facultyForm")
  const title = document.getElementById("facultyModalTitle")

  if (facultyId) {
    // Edit mode
    currentEditingFaculty = facultyData.find((f) => f.id === facultyId)
    title.textContent = "Edit Faculty"

    // Populate form
    document.getElementById("facultyName").value = currentEditingFaculty.name
    document.getElementById("facultyDepartment").value = currentEditingFaculty.department
    document.getElementById("facultyRole").value = currentEditingFaculty.role
    document.getElementById("facultyEmail").value = currentEditingFaculty.email
    document.getElementById("facultyPhone").value = currentEditingFaculty.phone || ""
    document.getElementById("facultyOffice").value = currentEditingFaculty.office || ""
    document.getElementById("facultyBio").value = currentEditingFaculty.bio || ""
    document.getElementById("facultyCourses").value = currentEditingFaculty.courses.join(", ")
  } else {
    // Add mode
    currentEditingFaculty = null
    title.textContent = "Add Faculty"
    form.reset()
  }

  modal.style.display = "block"
}

function closeFacultyModal() {
  document.getElementById("facultyModal").style.display = "none"
  currentEditingFaculty = null
}

function editFaculty(facultyId) {
  openFacultyModal(facultyId)
}

function deleteFaculty(facultyId) {
  if (confirm("Are you sure you want to delete this faculty member?")) {
    facultyData = facultyData.filter((f) => f.id !== facultyId)
    reviewsData = reviewsData.filter((r) => r.facultyId !== facultyId)

    localStorage.setItem("facultyData", JSON.stringify(facultyData))
    localStorage.setItem("reviewsData", JSON.stringify(reviewsData))

    updateFacultyGrid()
    updateDashboard()
  }
}

// Faculty form submission
document.getElementById("facultyForm").addEventListener("submit", (e) => {
  e.preventDefault()

  const formData = {
    name: document.getElementById("facultyName").value,
    department: document.getElementById("facultyDepartment").value,
    role: document.getElementById("facultyRole").value,
    email: document.getElementById("facultyEmail").value,
    phone: document.getElementById("facultyPhone").value,
    office: document.getElementById("facultyOffice").value,
    bio: document.getElementById("facultyBio").value,
    courses: document
      .getElementById("facultyCourses")
      .value.split(",")
      .map((c) => c.trim())
      .filter((c) => c),
  }

  if (currentEditingFaculty) {
    // Update existing faculty
    const index = facultyData.findIndex((f) => f.id === currentEditingFaculty.id)
    facultyData[index] = { ...facultyData[index], ...formData }
  } else {
    // Add new faculty
    const newFaculty = {
      id: Date.now(),
      ...formData,
      teachingRating: 0,
      markingRating: 0,
      overallRating: 0,
      totalReviews: 0,
      totalVotes: 0,
    }
    facultyData.push(newFaculty)
  }

  localStorage.setItem("facultyData", JSON.stringify(facultyData))
  updateFacultyGrid()
  updateDashboard()
  closeFacultyModal()
})

// Review functions
function updateReviewsList() {
  const reviewsList = document.getElementById("reviewsList")
  const filteredReviews = getFilteredReviews().filter((r) => r.status === "approved")

  reviewsList.innerHTML = filteredReviews
    .map(
      (review) => `
        <div class="review-card">
            <div class="review-header">
                <div class="review-info">
                    <h3>${review.facultyName} - ${review.course}</h3>
                    <div class="review-meta">
                        By ${review.reviewerName} on ${formatDate(review.date)}
                        <div class="semester-info">
                            <i class="fas fa-calendar-alt"></i> ${review.semester || "Semester not specified"}
                        </div>
                    </div>
                </div>
                <div class="review-rating">
                    ${generateReviewRatingDisplay(review)}
                </div>
            </div>
            <div class="review-content">
                ${review.comment}
            </div>
            <div class="review-actions">
                <div class="vote-buttons">
                    <button class="vote-btn ${review.userVote === "up" ? "upvoted" : ""}" 
                            onclick="voteReview(${review.id}, 'up')">
                        <i class="fas fa-thumbs-up"></i>
                        <span>${review.upvotes}</span>
                    </button>
                    <button class="vote-btn ${review.userVote === "down" ? "downvoted" : ""}" 
                            onclick="voteReview(${review.id}, 'down')">
                        <i class="fas fa-thumbs-down"></i>
                        <span>${review.downvotes}</span>
                    </button>
                </div>
                <small class="review-date">Review #${review.id}</small>
            </div>
        </div>
    `,
    )
    .join("")
}

function updatePendingReviewsList() {
  const pendingReviewsList = document.getElementById("pendingReviewsList")
  const pendingReviews = reviewsData.filter((r) => r.status === "pending")

  if (pendingReviews.length === 0) {
    pendingReviewsList.innerHTML = `
      <div class="review-card">
        <p style="text-align: center; color: #666; font-style: italic;">No pending reviews</p>
      </div>
    `
    return
  }

  pendingReviewsList.innerHTML = pendingReviews
    .map(
      (review) => `
        <div class="review-card pending">
            <div class="review-header">
                <div class="review-info">
                    <h3>${review.facultyName} - ${review.course}</h3>
                    <div class="review-meta">
                        By ${review.reviewerName} on ${formatDate(review.date)}
                        <span style="color: #f39c12; font-weight: bold; margin-left: 1rem;">PENDING APPROVAL</span>
                        <div class="semester-info">
                            <i class="fas fa-calendar-alt"></i> ${review.semester || "Semester not specified"}
                        </div>
                    </div>
                </div>
                <div class="review-rating">
                    ${generateReviewRatingDisplay(review)}
                </div>
            </div>
            <div class="review-content">
                ${review.comment}
            </div>
            <div class="admin-actions">
                <button class="btn-approve" onclick="approveReview(${review.id})">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="btn-reject" onclick="rejectReview(${review.id})">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
        </div>
    `,
    )
    .join("")
}

function getFilteredReviews() {
  const courseFilter = document.getElementById("courseFilter").value
  const facultyFilter = document.getElementById("facultyFilterReview").value

  return reviewsData
    .filter((review) => {
      const matchesCourse = !courseFilter || review.course === courseFilter
      const matchesFaculty = !facultyFilter || review.facultyName === facultyFilter

      return matchesCourse && matchesFaculty
    })
    .sort((a, b) => new Date(b.date) - new Date(a.date))
}

function filterReviews() {
  updateReviewsList()
}

function populateReviewFilters() {
  const courseFilter = document.getElementById("courseFilter")
  const facultyFilter = document.getElementById("facultyFilterReview")

  // Get unique courses from approved reviews
  const approvedReviews = reviewsData.filter((r) => r.status === "approved")
  const courses = [...new Set(approvedReviews.map((r) => r.course))]
  courseFilter.innerHTML =
    '<option value="">All Courses</option>' +
    courses.map((course) => `<option value="${course}">${course}</option>`).join("")

  // Get unique faculty from approved reviews
  const faculty = [...new Set(approvedReviews.map((r) => r.facultyName))]
  facultyFilter.innerHTML =
    '<option value="">All Faculty</option>' + faculty.map((name) => `<option value="${name}">${name}</option>`).join("")
}

// Review modal functions
function openReviewModal() {
  const modal = document.getElementById("reviewModal")
  const facultySelect = document.getElementById("reviewFaculty")

  // Populate faculty dropdown
  facultySelect.innerHTML =
    '<option value="">Select Faculty</option>' +
    facultyData.map((faculty) => `<option value="${faculty.id}">${faculty.name}</option>`).join("")

  modal.style.display = "block"
}

function closeReviewModal() {
  document.getElementById("reviewModal").style.display = "none"
  document.getElementById("reviewForm").reset()
  document.querySelectorAll('input[name="teachingRating"]').forEach((input) => (input.checked = false))
  document.querySelectorAll('input[name="markingRating"]').forEach((input) => (input.checked = false))
  document.getElementById("teachingRatingGroup").style.display = "none"
  document.getElementById("markingRatingGroup").style.display = "none"
  document.getElementById("reviewSemester").value = ""
}

// Setup review form
function setupReviewForm() {
  // Update course dropdown when faculty is selected
  document.getElementById("reviewFaculty").addEventListener("change", function () {
    const facultyId = Number.parseInt(this.value)
    const courseSelect = document.getElementById("reviewCourse")
    const teachingRatingGroup = document.getElementById("teachingRatingGroup")
    const markingRatingGroup = document.getElementById("markingRatingGroup")

    if (facultyId) {
      const faculty = facultyData.find((f) => f.id === facultyId)
      courseSelect.innerHTML =
        '<option value="">Select Course</option>' +
        faculty.courses.map((course) => `<option value="${course}">${course}</option>`).join("")
      courseSelect.disabled = false

      // Show appropriate rating groups based on faculty role
      if (faculty.role === "Teaching only") {
        teachingRatingGroup.style.display = "block"
        markingRatingGroup.style.display = "none"
        // Make teaching rating required
        document.querySelectorAll('input[name="teachingRating"]').forEach((input) => (input.required = true))
        document.querySelectorAll('input[name="markingRating"]').forEach((input) => (input.required = false))
      } else if (faculty.role === "Marking only") {
        teachingRatingGroup.style.display = "none"
        markingRatingGroup.style.display = "block"
        // Make marking rating required
        document.querySelectorAll('input[name="teachingRating"]').forEach((input) => (input.required = false))
        document.querySelectorAll('input[name="markingRating"]').forEach((input) => (input.required = true))
      } else if (faculty.role === "Teaching + Marking") {
        teachingRatingGroup.style.display = "block"
        markingRatingGroup.style.display = "block"
        // Make both ratings required
        document.querySelectorAll('input[name="teachingRating"]').forEach((input) => (input.required = true))
        document.querySelectorAll('input[name="markingRating"]').forEach((input) => (input.required = true))
      }
    } else {
      courseSelect.innerHTML = '<option value="">Select Course</option>'
      courseSelect.disabled = true
      teachingRatingGroup.style.display = "none"
      markingRatingGroup.style.display = "none"
      // Reset requirements
      document.querySelectorAll('input[name="teachingRating"]').forEach((input) => (input.required = false))
      document.querySelectorAll('input[name="markingRating"]').forEach((input) => (input.required = false))
    }
  })
}

// Review form submission
document.getElementById("reviewForm").addEventListener("submit", (e) => {
  e.preventDefault()

  const facultyId = Number.parseInt(document.getElementById("reviewFaculty").value)
  const faculty = facultyData.find((f) => f.id === facultyId)

  const teachingRatingInput = document.querySelector('input[name="teachingRating"]:checked')
  const markingRatingInput = document.querySelector('input[name="markingRating"]:checked')

  // Validate required ratings based on faculty role
  let validationError = ""

  if (faculty.role === "Teaching only" && !teachingRatingInput) {
    validationError = "Please provide a teaching rating for this faculty member."
  } else if (faculty.role === "Marking only" && !markingRatingInput) {
    validationError = "Please provide a marking rating for this faculty member."
  } else if (faculty.role === "Teaching + Marking") {
    if (!teachingRatingInput && !markingRatingInput) {
      validationError = "Please provide both teaching and marking ratings for this faculty member."
    } else if (!teachingRatingInput) {
      validationError = "Please provide a teaching rating for this faculty member."
    } else if (!markingRatingInput) {
      validationError = "Please provide a marking rating for this faculty member."
    }
  }

  if (validationError) {
    alert(validationError)
    return
  }

  const newReview = {
    id: Date.now(),
    facultyId: facultyId,
    facultyName: faculty.name,
    course: document.getElementById("reviewCourse").value,
    semester: document.getElementById("reviewSemester").value,
    teachingRating: teachingRatingInput ? Number.parseInt(teachingRatingInput.value) : null,
    markingRating: markingRatingInput ? Number.parseInt(markingRatingInput.value) : null,
    comment: document.getElementById("reviewComment").value,
    reviewerName: document.getElementById("reviewerName").value,
    date: new Date().toISOString().split("T")[0],
    upvotes: 0,
    downvotes: 0,
    userVote: null,
    status: "pending", // All reviews start as pending
  }

  reviewsData.push(newReview)
  localStorage.setItem("reviewsData", JSON.stringify(reviewsData))

  updateDashboard()
  updatePendingReviewsList()
  closeReviewModal()

  // Show success message with specific information about what was rated
  let ratingInfo = ""
  if (faculty.role === "Teaching only") {
    ratingInfo = "teaching performance"
  } else if (faculty.role === "Marking only") {
    ratingInfo = "marking performance"
  } else {
    ratingInfo = "teaching and marking performance"
  }

  alert(
    `Review submitted successfully! You rated ${faculty.name}'s ${ratingInfo} for ${newReview.semester}. Your review will be visible after admin approval.`,
  )
})

// Admin review approval functions
function approveReview(reviewId) {
  const review = reviewsData.find((r) => r.id === reviewId)
  if (review) {
    review.status = "approved"

    // Update faculty ratings
    updateFacultyRatings(review.facultyId)

    localStorage.setItem("reviewsData", JSON.stringify(reviewsData))
    localStorage.setItem("facultyData", JSON.stringify(facultyData))

    updateDashboard()
    updatePendingReviewsList()
    updateReviewsList()
    updateFacultyGrid()
  }
}

function rejectReview(reviewId) {
  if (confirm("Are you sure you want to reject this review? This action cannot be undone.")) {
    reviewsData = reviewsData.filter((r) => r.id !== reviewId)
    localStorage.setItem("reviewsData", JSON.stringify(reviewsData))

    updateDashboard()
    updatePendingReviewsList()
  }
}

function updateFacultyRatings(facultyId) {
  const faculty = facultyData.find((f) => f.id === facultyId)
  const facultyReviews = reviewsData.filter((r) => r.facultyId === facultyId && r.status === "approved")

  if (facultyReviews.length > 0) {
    // Calculate teaching rating average
    const teachingReviews = facultyReviews.filter((r) => r.teachingRating !== null)
    if (teachingReviews.length > 0) {
      faculty.teachingRating = teachingReviews.reduce((sum, r) => sum + r.teachingRating, 0) / teachingReviews.length
    }

    // Calculate marking rating average
    const markingReviews = facultyReviews.filter((r) => r.markingRating !== null)
    if (markingReviews.length > 0) {
      faculty.markingRating = markingReviews.reduce((sum, r) => sum + r.markingRating, 0) / markingReviews.length
    }

    // Calculate overall rating
    let totalRating = 0
    let ratingCount = 0

    if (faculty.teachingRating > 0) {
      totalRating += faculty.teachingRating
      ratingCount++
    }
    if (faculty.markingRating > 0) {
      totalRating += faculty.markingRating
      ratingCount++
    }

    faculty.overallRating = ratingCount > 0 ? totalRating / ratingCount : 0
    faculty.totalReviews = facultyReviews.length
    faculty.totalVotes = facultyReviews.reduce((sum, r) => sum + r.upvotes + r.downvotes, 0)
  }
}

// Voting functions
function voteReview(reviewId, voteType) {
  const review = reviewsData.find((r) => r.id === reviewId)

  if (review.userVote === voteType) {
    // Remove vote
    if (voteType === "up") {
      review.upvotes--
    } else {
      review.downvotes--
    }
    review.userVote = null
  } else {
    // Change or add vote
    if (review.userVote === "up") {
      review.upvotes--
    } else if (review.userVote === "down") {
      review.downvotes--
    }

    if (voteType === "up") {
      review.upvotes++
    } else {
      review.downvotes++
    }
    review.userVote = voteType
  }

  // Update faculty total votes
  const faculty = facultyData.find((f) => f.id === review.facultyId)
  faculty.totalVotes = reviewsData
    .filter((r) => r.facultyId === faculty.id && r.status === "approved")
    .reduce((sum, r) => sum + r.upvotes + r.downvotes, 0)

  localStorage.setItem("reviewsData", JSON.stringify(reviewsData))
  localStorage.setItem("facultyData", JSON.stringify(facultyData))

  updateReviewsList()
  updateDashboard()
}

function viewFacultyReviews(facultyId) {
  const faculty = facultyData.find((f) => f.id === facultyId)
  document.getElementById("facultyFilterReview").value = faculty.name
  showSection("reviews")
  filterReviews()
}

// Leaderboard functions
function updateLeaderboard() {
  const type = document.getElementById("leaderboardType").value
  const limit = Number.parseInt(document.getElementById("leaderboardLimit").value)

  const sortedFaculty = [...facultyData]

  switch (type) {
    case "rating":
      sortedFaculty.sort((a, b) => b.overallRating - a.overallRating)
      break
    case "reviews":
      sortedFaculty.sort((a, b) => b.totalReviews - a.totalReviews)
      break
    case "votes":
      sortedFaculty.sort((a, b) => b.totalVotes - a.totalVotes)
      break
  }

  const topFaculty = sortedFaculty.slice(0, limit)
  const leaderboardList = document.getElementById("leaderboardList")

  leaderboardList.innerHTML = topFaculty
    .map((faculty, index) => {
      const rank = index + 1
      let rankClass = ""
      if (rank === 1) rankClass = "gold"
      else if (rank === 2) rankClass = "silver"
      else if (rank === 3) rankClass = "bronze"

      let score, label
      switch (type) {
        case "rating":
          score = faculty.overallRating.toFixed(1)
          label = "Rating"
          break
        case "reviews":
          score = faculty.totalReviews
          label = "Reviews"
          break
        case "votes":
          score = faculty.totalVotes
          label = "Votes"
          break
      }

      return `
            <div class="leaderboard-item">
                <div class="leaderboard-rank ${rankClass}">
                    ${rank <= 3 ? ["🥇", "🥈", "🥉"][rank - 1] : rank}
                </div>
                <div class="leaderboard-avatar">
                    ${faculty.name
                      .split(" ")
                      .map((n) => n[0])
                      .join("")}
                </div>
                <div class="leaderboard-info">
                    <h3>${faculty.name}</h3>
                    <p>${faculty.department}</p>
                </div>
                <div class="leaderboard-score">
                    <div class="score">${score}</div>
                    <div class="label">${label}</div>
                </div>
            </div>
        `
    })
    .join("")
}

// Utility functions
function generateRatingDisplay(faculty) {
  let display = `<div style="margin-bottom: 0.5rem;"><strong>Overall: ${generateStars(faculty.overallRating)} (${faculty.overallRating.toFixed(1)})</strong></div>`

  if (faculty.role === "Teaching only" || faculty.role === "Teaching + Marking") {
    display += `<div style="font-size: 0.9rem;">Teaching: ${generateStars(faculty.teachingRating)} (${faculty.teachingRating.toFixed(1)})</div>`
  }

  if (faculty.role === "Marking only" || faculty.role === "Teaching + Marking") {
    display += `<div style="font-size: 0.9rem;">Marking: ${generateStars(faculty.markingRating)} (${faculty.markingRating.toFixed(1)})</div>`
  }

  display += `<div style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">${faculty.totalReviews} reviews</div>`

  return display
}

function generateReviewRatingDisplay(review) {
  let display = ""

  if (review.teachingRating !== null) {
    display += `<div>Teaching: ${generateStars(review.teachingRating)}</div>`
  }

  if (review.markingRating !== null) {
    display += `<div>Marking: ${generateStars(review.markingRating)}</div>`
  }

  return display
}

function generateStars(rating) {
  if (rating >= 2.5) {
    return "😊 Good"
  } else if (rating >= 1.5) {
    return "😐 Average"
  } else if (rating >= 1) {
    return "😞 Bad"
  } else {
    return "No Rating"
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  })
}

// Close modals when clicking outside
window.addEventListener("click", (event) => {
  const facultyModal = document.getElementById("facultyModal")
  const reviewModal = document.getElementById("reviewModal")

  if (event.target === facultyModal) {
    closeFacultyModal()
  }
  if (event.target === reviewModal) {
    closeReviewModal()
  }
})

// Keyboard shortcuts
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeFacultyModal()
    closeReviewModal()
  }
})
