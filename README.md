# MariAdmin

**MariAdmin** is a lightweight, responsive PHP-based SQL admin panel designed specifically for managing **MariaDB** databases. It is ideal for junior developers or anyone who needs a simple and fast interface to execute queries and manage their databases without the overhead of large tools like phpMyAdmin.

---

## ✨ Features

- 🔐 Secure login using real MySQL/MariaDB credentials
- 🧩 Lightweight and fast – no external dependencies beyond Bootstrap
- 🧾 SQL console with:
  - Query history
  - Syntax templates (INSERT, UPDATE, DELETE)
  - Auto "USE database" context awareness
- 📦 Responsive table view with pagination
- 🧠 Auto-detect & list databases
- 🗃️ Clickable tables to auto-generate `SELECT` queries
- 📋 Simple notification of execution status or error
- 🔍 PHP Info viewer (popup)
- 🌙 Built-in dark mode using Bootstrap 5.3

---

## ⚙️ Requirements

- PHP 7.4 or higher (tested up to PHP 8.3)
- MariaDB or MySQL
- Web server (Apache, Nginx, etc.)

---

## 🚀 How to Use

1. Clone or copy this repo into your web directory.
2. Open the `index.php` (or `mysql/index.php`) from your browser.
3. Login using your **MariaDB** username, password, and host (default: `localhost`).
4. Start running queries and exploring your databases!

---

## 📁 Folder Structure

```

/public/
└── mysql/
└── index.php       # Main UI for MariAdmin
README.md

```

---

## 📣 Why MariAdmin?

This tool is built for speed and clarity. Junior developers often struggle with large admin panels. MariAdmin simplifies the SQL management experience by focusing only on what matters most — the database and your queries.

---

## 🧠 Credits

Built with ❤️ by **Sabily Enterprise**  
Powered by Bootstrap 5.3

---

## 🛡️ License

This project is open-source and free to use for personal and commercial purposes.

