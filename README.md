# Interactive English Lab

A comprehensive English learning platform built with PHP, MySQL, and Bootstrap. This platform allows students to enroll in courses, complete lessons, take quizzes, and track their learning progress with XP and streak systems.

## Features

### For Students
- 📚 Browse and enroll in English courses
- 📖 Complete interactive lessons with video support
- 🎯 Take quizzes to test knowledge and earn XP
- 📊 Track learning progress with XP points and daily streaks
- 👤 User profile management

### For Administrators
- 🎓 Full CRUD operations for Courses, Lessons, and Quizzes
- 👥 Manage student enrollments
- 📈 View dashboard with statistics and recent activities
- 🎨 Upload images via file upload or URL
- 📝 Edit all content including lessons and quizzes

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: Bootstrap 5, HTML5, CSS3, JavaScript
- **Server**: XAMPP (Apache + MySQL)

## Installation

### Prerequisites
- XAMPP (or any PHP server with MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/englishlab.git
   cd englishlab
   ```

2. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `englishlab`
   - Import the SQL file: `config/database.sql`

3. **Configuration**
   - Copy `config/db.php.example` to `config/db.php` (if exists)
   - Or create `config/db.php` with your database credentials:
   ```php
   <?php
   $host = 'localhost';
   $dbname = 'englishlab';
   $username = 'root';
   $password = '';
   
   $conn = new mysqli($host, $username, $password, $dbname);
   // ... rest of configuration
   ```

4. **File Permissions**
   - Ensure `assets/img/uploads/` and `assets/uploads/courses/` directories are writable

5. **Access the Application**
   - Start XAMPP (Apache + MySQL)
   - Navigate to: `http://localhost/englishlab`

## Default Admin Account

After importing the database, you can login with:
- **Email**: admin@ielab.local
- **Password**: admin123

⚠️ **Important**: Change the admin password after first login!

## Project Structure

```
englishlab/
├── admin/              # Admin panel pages
│   ├── add_*.php      # Add new content
│   ├── edit_*.php     # Edit existing content
│   ├── delete_*.php   # Delete content
│   └── list_*.php     # List all content
├── assets/            # Static files
│   ├── css/           # Stylesheets
│   ├── img/           # Images
│   └── js/            # JavaScript files
├── config/            # Configuration files
│   ├── db.php         # Database connection (not in repo)
│   └── database.sql   # Database schema
├── partials/          # Reusable components
│   └── navbar.php     # Navigation bar
└── *.php             # Main application pages
```

## Features in Detail

### Course Management
- Create, edit, and delete courses
- Upload course images or use image URLs
- Track student enrollments per course

### Lesson Management
- Create lessons with rich content
- Add YouTube video embeds
- Upload images or use image URLs
- Set lesson order and duration

### Quiz Management
- Create multiple choice quizzes
- Link quizzes to specific courses/lessons
- Set XP points for each quiz
- Track student quiz results

### Student Management
- View all students and their progress
- Track XP and streak statistics
- View enrolled courses and quiz results
- Export student data to CSV

## Security Notes

- ⚠️ Never commit `config/db.php` to version control
- ⚠️ Change default admin credentials
- ⚠️ Use prepared statements (already implemented)
- ⚠️ Validate and sanitize all user inputs (already implemented)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues and questions, please open an issue on GitHub.

## Author

Created with ❤️ for English learners worldwide.

