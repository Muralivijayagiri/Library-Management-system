# Library Management System

A comprehensive, web-based Library Management System designed to streamline library operations, automate book tracking, and manage multimedia resources efficiently. Built using PHP, MySQL, and styled with custom assets, it features sub-modules for administrators, students, and an embedded PDF/Audio reader engine.

## 🚀 Features

- **Dual-Role Dashboard**: Custom interfaces and authorization logic for both Admins and Students.
- **Advanced Asset Tracking**: Dedicated modules to manage physical books, electronic documents, audio files, and profiles.
- **Embedded Document Viewer**: Integrated `pdfjs` rendering engine to view books and reading materials natively in the browser.
- **Internal Chat Subsystem**: Built-in chat module for communication or user support queries.
- **Migration Ready**: Includes pre-configured database schema migrations for predictable environment setups.

## 📂 Project Architecture

```text
Library_Managment/
├── .vscode/             # Editor workspace settings
├── admin/               # Administrative backend and APIs
├── assets/              # Main UI styling, icons, and image packs
├── chat/                # Live messaging components
├── config/              # Central configurations and DB migrations
├── includes/            # Reusable PHP partials, functions, and layout templates
├── pdfjs/               # PDF rendering library and asset pipeline
├── student/             # Student portal dashboards and APIs
└── uploads/             # Managed system directories for files and documents
```

## 🛠️ Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend Frameworks:** HTML5, CSS3, JavaScript (ES6)
- **Document Rendering:** PDF.js v2.x

## 💻 Local Installation & Setup

Follow these steps to deploy this repository on your local system using **XAMPP**:

1. **Clone or Move the Repository:**
   Ensure this project directory sits directly inside your local server's web root:
   ```bash
   C:\xampp\htdocs\Library_Managment\
   ```

2. **Database Initialization:**
   - Launch the **XAMPP Control Panel** and activate both **Apache** and **MySQL**.
   - Navigate to `http://localhost/phpmyadmin` in your web browser.
   - Create a database named `library_management` (this is the name used by `config/db.php`).
   - Import `config/database.sql` for a fresh install, and apply the relevant patches in `config/migrations/` for an existing database. There is no automatic migration runner.
   - If a page reports an unknown column, compare its query with the live table using `DESCRIBE`.

3. **Verify Local Configuration:**
   - Check the primary server files under the `config/` directory.
   - `config/db.php` defaults to the XAMPP user `root` with a blank password; adjust it if your local credentials differ.
   - The upload directories (`uploads/book_covers`, `uploads/audio`, `uploads/audio_images`, `uploads/documents`, and `uploads/student_profiles`) are preserved with `.gitkeep` files; uploaded content itself remains gitignored.

4. **Launch Application:**
   - Open your web browser and navigate to:
     ```text
     http://localhost/Library_Managment
     ```

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).
