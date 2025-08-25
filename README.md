# Faculty Review System - Express.js Version

A complete faculty review and rating system built with Express.js and MySQL, converted from Next.js TypeScript following the CSE370 manual guidelines.

## Features

- **Authentication System**: Student and admin login with role-based access control
- **Faculty Management**: CRUD operations for faculty members
- **Department Management**: Organize faculty by departments
- **Course Management**: Track courses and faculty assignments
- **Review System**: Students can submit reviews, admins can approve them
- **Rating System**: Automatic calculation of faculty ratings
- **Dashboard**: Statistics and analytics
- **Leaderboard**: Faculty ranking system

## Technology Stack

- **Backend**: Express.js (Node.js)
- **Database**: MySQL with connection pooling
- **Authentication**: bcryptjs for password hashing
- **API**: RESTful endpoints with JSON responses

## Setup Instructions

### 1. Prerequisites

- Node.js (v14 or higher)
- MySQL Server
- MySQL Workbench (optional, for GUI management)

### 2. Install Dependencies

\`\`\`bash
npm install
\`\`\`

### 3. Database Setup

1. **Create Database**: Run the SQL scripts in order:
   - `scripts/00-local-mysql-setup.sql`
   - `scripts/01-create-database.sql`
   - `scripts/02-seed-data.sql`
   - `scripts/03-update-ratings.sql`
   - `scripts/04-fix-passwords.sql`

2. **Environment Variables**: Create a `.env` file:
   \`\`\`
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=your_mysql_password
   DB_NAME=faculty_review_db
   DB_PORT=3306
   PORT=3001
   \`\`\`

### 4. Run the Application

\`\`\`bash
# Development mode
npm run dev

# Production mode
npm start
\`\`\`

The server will start on `http://localhost:3001`

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login (student/admin)

### Departments
- `GET /api/departments` - Get all departments
- `POST /api/departments` - Add new department (admin only)

### Faculty
- `GET /api/faculty` - Get all faculty (with search/filter)
- `POST /api/faculty` - Add new faculty (admin only)
- `GET /api/faculty/:initial` - Get faculty by initial
- `PUT /api/faculty/:initial` - Update faculty (admin only)
- `DELETE /api/faculty/:initial` - Delete faculty (admin only)

### Courses
- `GET /api/courses` - Get all courses
- `POST /api/courses` - Add new course (admin only)

### Reviews
- `GET /api/reviews` - Get reviews (filtered by role)
- `POST /api/reviews` - Submit new review (student only)
- `PUT /api/reviews/:id/approve` - Approve review (admin only)

### Analytics
- `GET /api/dashboard/stats` - Get dashboard statistics
- `GET /api/leaderboard` - Get faculty leaderboard

## Testing

### Automated Testing
Run the comprehensive test suite:
\`\`\`bash
node test-api.js
\`\`\`

### Manual Testing
1. Visit `http://localhost:3001` for the test interface
2. Use the HTML form to test login functionality
3. Test API endpoints using the provided buttons

### Sample Login Credentials
- **Admin**: `admin001` / `admin123`
- **Student**: `2021001` / `student123`

## Database Schema

### Key Tables
- `admins` - Admin user accounts
- `students` - Student user accounts
- `departments` - Academic departments
- `faculty` - Faculty members with ratings
- `courses` - Course information
- `reviews` - Student reviews of faculty

### Features
- **Automatic Rating Calculation**: Stored procedures update faculty ratings
- **Review Approval System**: Admin approval required for reviews
- **Role-Based Access**: Different permissions for students and admins

## Project Structure

\`\`\`
├── app.js                 # Main Express server
├── models/
│   └── db.js             # Database connection pool
├── utils/
│   └── auth.js           # Authentication utilities
├── public/
│   └── index.html        # Test interface
├── scripts/              # Database setup scripts
├── test-api.js           # API testing suite
├── package.json          # Dependencies
├── .env                  # Environment variables
└── README.md            # This file
\`\`\`

## Development Notes

- Uses MySQL connection pooling for better performance
- Implements proper error handling and validation
- Follows RESTful API conventions
- Includes comprehensive authentication middleware
- Supports both bcrypt hashed and plain text passwords (for development)

## Troubleshooting

1. **Database Connection Issues**:
   - Check MySQL server is running
   - Verify environment variables in `.env`
   - Ensure database and tables exist

2. **Authentication Problems**:
   - Run the password fix script: `scripts/04-fix-passwords.sql`
   - Check user credentials in database

3. **API Errors**:
   - Check server logs for detailed error messages
   - Verify request headers include proper authentication
   - Use the test suite to identify specific issues

## Contributing

1. Follow the existing code structure
2. Add tests for new features
3. Update documentation as needed
4. Ensure all tests pass before submitting changes
