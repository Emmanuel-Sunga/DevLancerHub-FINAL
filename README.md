# DevLancerHub

A comprehensive freelancing platform designed for IT and Computer Science professionals. Connect talented freelancers with employers seeking technical expertise.

## 🚀 Features

### For Freelancers (Employees)
- **Browse Available Jobs**: Search and filter job postings by type, location, and keywords
- **Apply to Jobs**: Submit applications with optional cover letters
- **Track Applications**: View status of all your job applications (Pending/Accepted/Rejected)
- **Profile Management**: Create and update your professional profile with skills, experience, and bio
- **Payment Tracking**: View earnings and payment history
- **Messaging System**: Communicate with employers
- **Friend System**: Connect with other professionals

### For Employers (Clients)
- **Post Jobs**: Create detailed job postings with requirements, budget, and deadlines
- **Manage Job Postings**: Edit or delete your job postings with an intuitive edit modal
- **View Applications**: See all applications from freelancers, including cover letters
- **Organized Application Management**: 
  - Applications sorted alphabetically by applicant name
  - Separate sections for Pending and Accepted applications
  - Applications automatically removed once payment is completed
- **One-Click Actions**: Accept or reject applications with a single click
- **Integrated Payment System**: 
  - Pay employees directly from accepted applications
  - Select employees from accepted applications (no manual ID entry)
  - Track payment status and history
- **Browse Freelancers**: Discover talented professionals and view their profiles
- **Messaging System**: Communicate with freelancers
- **Friend System**: Build your professional network

### General Features
- **User Authentication**: Secure registration and login system
- **Role-Based Access**: Separate dashboards for freelancers and employers
- **Advanced Search & Filtering**: 
  - Real-time search by title, skills, or location
  - Filter by job type (Full-time, Part-time, Contract, Freelance)
  - Filter by location
  - Instant results as you type
- **Responsive Design**: Works on desktop and mobile devices
- **Modern UI**: Clean and intuitive interface with optimized button and container sizes
- **Smart Application Management**: Automatic organization and cleanup of applications

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Architecture**: MVC (Model-View-Controller) pattern
- **Data Storage**: JSON-based file system
- **Frontend**: HTML5, CSS3, JavaScript
- **Dependency Management**: Composer
- **Password Security**: PHP password_hash() with bcrypt

## 📋 Requirements

- PHP 7.4 or higher
- Composer (for dependency management)
- Web server (Apache/Nginx) or PHP built-in server
- Write permissions for the `data/` directory

## 🔧 Installation

### Step 1: Clone or Download the Project
```bash
# If using git
git clone <repository-url>
cd DevLancerHub1Copy

# Or extract the project to your web server directory
```

### Step 2: Install Dependencies
```bash
composer install
```

This will install the autoloader and set up the project structure.

### Step 3: Configure the Application

The application uses JSON files for data storage. The data directory structure is automatically created, but ensure the `data/` folder has write permissions:

```bash
# On Linux/Mac
chmod -R 755 data/

# On Windows, ensure the folder has write permissions
```

### Step 4: Set Up Web Server

#### Option A: Using XAMPP/WAMP/MAMP
1. Copy the project to your web server directory:
   - XAMPP: `C:\xampp\htdocs\DevLancerHub1Copy`
   - WAMP: `C:\wamp64\www\DevLancerHub1Copy`
   - MAMP: `/Applications/MAMP/htdocs/DevLancerHub1Copy`

2. Access via: `http://localhost/DevLancerHub1Copy/public/`

#### Option B: Using PHP Built-in Server
```bash
cd public
php -S localhost:8000
```

Then access: `http://localhost:8000`

## 📁 Project Structure

```
DevLancerHub1Copy/
├── config/                 # Configuration files
│   ├── app.php            # Application settings
│   └── database.php       # Database (JSON files) configuration
├── data/                  # JSON data storage
│   ├── users.json        # User accounts
│   ├── jobs.json         # Job postings
│   ├── applications.json # Job applications
│   ├── payments.json     # Payment records
│   ├── messages.json     # User messages
│   └── friends.json      # Friend connections
├── public/               # Public web files
│   ├── css/
│   │   └── style.css    # Main stylesheet
│   ├── js/
│   │   ├── dashboard.js
│   │   ├── profile.js
│   │   └── register.js
│   ├── index.php        # Homepage
│   ├── login.php        # Login page
│   ├── register.php     # Registration page
│   ├── dashboard.php    # Main dashboard
│   ├── profile.php      # User profiles
│   ├── payments.php     # Payment management
│   ├── messages.php     # Messaging system
│   └── friends.php      # Friend management
├── src/                  # Application source code
│   ├── Controllers/     # Request handlers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── JobController.php
│   │   ├── ApplicationController.php
│   │   ├── PaymentController.php
│   │   ├── ProfileController.php
│   │   ├── MessageController.php
│   │   └── FriendController.php
│   ├── Models/          # Data models
│   │   ├── User.php
│   │   ├── Job.php
│   │   ├── Application.php
│   │   ├── Payment.php
│   │   ├── Message.php
│   │   └── Friend.php
│   ├── Services/        # Business logic
│   │   ├── AuthService.php
│   │   ├── JobService.php
│   │   ├── ApplicationService.php
│   │   ├── PaymentService.php
│   │   ├── MessageService.php
│   │   ├── FriendService.php
│   │   ├── ValidationService.php
│   │   └── JsonDatabase.php
│   ├── Middleware/      # Request middleware
│   │   ├── AuthMiddleware.php
│   │   └── SessionMiddleware.php
│   └── Exceptions/      # Custom exceptions
│       ├── AuthenticationException.php
│       └── ValidationException.php
├── vendor/              # Composer dependencies
├── composer.json        # Composer configuration
└── README.md           # This file
```

## 🎯 Usage Guide

### Getting Started

1. **Register an Account**
   - Visit the homepage
   - Click "Get Started" or "Register"
   - Choose your role: Freelancer or Employer
   - Fill in your details (name, email, password, location, etc.)
   - For freelancers: Add your skills and experience
   - Submit the form

2. **Login**
   - Go to the login page
   - Enter your email and password
   - You'll be redirected to your dashboard

### For Freelancers

1. **Browse Jobs**
   - On the dashboard, you'll see available jobs
   - Use the search bar to find specific jobs
   - Filter by job type (Full-time, Part-time, Contract, Freelance)
   - Filter by location

2. **Apply to Jobs**
   - Click "Apply Now" on any job posting
   - Optionally add a cover letter
   - Submit your application

3. **Track Applications**
   - View all your applications in the "My Applications" section
   - See the status: Pending, Accepted, or Rejected

4. **Manage Profile**
   - Click "My Profile" in the navigation
   - Update your skills, bio, experience, and location

### For Employers

1. **Post a Job**
   - On the dashboard, fill out the "Post a New Job" form
   - Enter job title, description, required skills, budget, duration, location
   - Optionally set a deadline
   - Click "Post Job"

2. **Manage Jobs**
   - View all your job postings in "My Job Postings"
   - Click "Edit" to modify job details using the edit modal
   - Delete jobs as needed
   - All changes are saved immediately

3. **Review Applications**
   - Applications are organized in two sections:
     - **Pending Applications**: New applications awaiting your review
     - **Accepted Applications**: Applications ready for payment processing
   - Applications are automatically sorted alphabetically by applicant name
   - View freelancer details, skills, experience, and cover letters
   - Accept or reject applications with one click
   - Applications are automatically removed once payment is completed

4. **Pay Employees**
   - From the "Accepted Applications" section, click "Pay Employee"
   - Payment modal opens with pre-filled employee and job information
   - Enter payment amount and select payment method
   - Create and manage payments
   - Mark payments as completed when done
   - On the Payments page, select a job to see only accepted applicants for that job

## 🔐 Security Features

- Password hashing using bcrypt
- Session-based authentication
- Role-based access control
- Input validation and sanitization
- CSRF protection (via session tokens)
- SQL injection prevention (using JSON storage)

## 📝 Configuration

### Application Settings (`config/app.php`)
- `app_name`: Application name
- `base_url`: Base URL for the application
- `session_lifetime`: Session duration in seconds
- `data_dir`: Directory for JSON data files

### Database Settings (`config/database.php`)
- Configure paths to JSON data files
- All data is stored in the `data/` directory

## 🐛 Troubleshooting

### Common Issues

**Issue: "Permission denied" errors**
- Solution: Ensure the `data/` directory has write permissions

**Issue: Composer autoload not working**
- Solution: Run `composer dump-autoload`

**Issue: Sessions not working**
- Solution: Check PHP session configuration and ensure session directory is writable

**Issue: Page not found errors**
- Solution: Ensure you're accessing files through the `public/` directory or configure your web server document root

**Issue: Applications not appearing after payment**
- Solution: This is expected behavior - applications are automatically removed once payment is completed. Check the Payments page to view payment history.

**Issue: Job posting not updating dashboard**
- Solution: Ensure you're using relative paths. The redirect has been fixed to use `dashboard.php` instead of `/dashboard.php`

## ✨ Recent Updates & Improvements

### Latest Features (v2.0)

#### Enhanced Application Management
- ✅ Applications sorted alphabetically by applicant name for easy navigation
- ✅ Separate sections for **Pending Applications** and **Accepted Applications**
- ✅ Automatic removal of applications once payment is completed (no manual cleanup needed)
- ✅ Clear visual distinction between application statuses with color-coded badges

#### Improved Payment System
- ✅ Direct "Pay Employee" button on accepted application cards
- ✅ Payment modal with pre-filled employee and job information
- ✅ Smart employee selection on Payments page - only shows accepted applicants for selected job
- ✅ No more manual employee ID entry required
- ✅ Seamless integration between applications and payments

#### Job Management Enhancements
- ✅ Edit jobs with a user-friendly modal interface
- ✅ All job fields can be updated (title, description, skills, budget, duration, location, deadline)
- ✅ Fixed job posting redirect issue - dashboard now updates immediately after posting
- ✅ Improved job posting workflow with better error handling

#### Advanced Search & Filtering
- ✅ Real-time job search as you type (searches title, skills, and location)
- ✅ Multiple filter options (job type, location)
- ✅ Instant results without page refresh
- ✅ Search and filters work together for precise job matching

#### UI/UX Improvements
- ✅ Optimized button and container sizes for better proportions
- ✅ Improved spacing and padding throughout the interface
- ✅ Better text and label sizing
- ✅ Enhanced modal interactions with click-outside-to-close
- ✅ Responsive design improvements for mobile devices

## 🚧 Future Enhancements

Potential features for future development:
- Email notifications for applications and payments
- File uploads (resumes, portfolios)
- Rating and review system
- Real-time chat improvements
- Payment gateway integration (Stripe, PayPal)
- Admin dashboard
- Analytics and reporting
- Application export functionality
- Bulk actions for applications

## 📄 License

This project is open source and available for educational purposes.

## 👥 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bugs and feature requests.

## 📞 Support

For issues, questions, or contributions, please open an issue in the project repository.

---

**Built with ❤️ for IT and Computer Science professionals**

#   O O P - F I N A L - P R O J E C T 
 
 #   O O P - F I N A L - P R O J E C T 
 
 