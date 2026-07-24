Lost & Found Management System

A secure and user-friendly Lost & Found Management System developed using PHP, MySQL, HTML, CSS, and JavaScript. The application provides a centralized platform for reporting, managing, and recovering lost items within educational institutions or organizations. It includes secure user authentication with Email OTP verification, item reporting, claim management, email notifications, and an Admin Analytics Dashboard for monitoring system activity.

---

Features

User Module

* User registration and login
* Email OTP verification for secure account creation
* Report lost items
* Report found items
* Upload item images
* Search and filter lost/found items
* Submit claim requests
* Track claim status
* Receive email notifications

Admin Module

* Secure admin login
* Interactive analytics dashboard
* View total users, lost items, found items, and claims
* Manage users
* Manage lost and found item reports
* Review and approve/reject claim requests
* Send email notifications
* Monitor overall system activity

---

Admin Analytics Dashboard

The dashboard provides administrators with an overview of the platform through statistics and visual insights, including:

* Total registered users
* Lost item reports
* Found item reports
* Pending and completed claims
* Overall platform activity

---

Technologies Used

| Technology | Purpose                                |
| ---------- | -------------------------------------- |
| PHP        | Backend Development                    |
| MySQL      | Database Management                    |
| HTML5      | Structure                              |
| CSS3       | Styling                                |
| JavaScript | Client-side Functionality              |
| XAMPP      | Local Development Environment          |
| PHPMailer  | Email OTP Verification & Notifications |

---

Project Structure

```
Lost-and-Found-System/
│
├── admin/              # Admin panel
├── user/               # User dashboard
├── assets/             # CSS, JS, Images
├── database/           # SQL database
├── includes/           # Configuration files
├── uploads/            # Uploaded item images
├── login.php
├── register.php
├── dashboard.php
├── index.php
└── README.md
```

---

Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/your-username/lost-found-management-system.git
   ```

2. Move the project folder to the htdocs directory in XAMPP.

3. Start Apache and MySQL from the XAMPP Control Panel.

4. Create a MySQL database.

5. Import the SQL file from the `database` folder.

6. Update the database credentials in the configuration file if needed.

7. Open your browser and visit:

   ```
   http://localhost/Lost-and-Found-System
   ```

---

Security Features

* Email OTP verification during registration
* Secure authentication system
* Admin-controlled claim verification
* Input validation
* File upload handling
* Email notifications

---

Project Objectives

* Provide a centralized platform for lost and found items.
* Reduce the time required to recover lost belongings.
* Improve communication between users and administrators.
* Ensure secure account creation through OTP verification.
* Simplify claim verification and item management.

---

Future Enhancements

* AI-based image matching
* QR code integration
* Mobile application
* Real-time notifications
* Advanced analytics and reports
* Multi-institution support

---

Author

Nived Sugunan

MCA Student

---

License

This project is developed for educational purposes. Feel free to use and modify it for learning and non-commercial projects.
