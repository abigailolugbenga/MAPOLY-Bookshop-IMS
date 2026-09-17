# MAPOLY Bookshop Inventory Management System

A web-based Inventory Management System built for the MAPOLY Bookshop as a
final year ND project (Computer Science, Moshood Abiola Polytechnic,
Abeokuta). Built with PHP, MySQL, and Bootstrap.

## Features

- **Authentication & role-based access control** — Admin and Store Officer
  roles, enforced server-side (not just hidden buttons). Passwords are
  hashed with bcrypt.
- **Book management** — add, edit, delete books, with category/supplier
  assignment and stock tracking.
- **Category management** — add, edit, delete categories, with input
  validation (letters only, no stray symbols/numbers).
- **Supplier management** — add, edit, delete suppliers, with name, contact
  person, phone number, and address, plus validation on each field.
- **Sales recording** — Store Officers record sales; stock is deducted
  inside a database transaction with row-level locking, so two
  simultaneous sales on the same book can never push stock negative.
  Selling more than available stock is blocked with an error.
- **Restock (purchase) recording** — Admins record stock received from
  suppliers; stock is added back accordingly.
- **Automated low-stock alerts** — any book at or below its reorder level
  is automatically flagged on the Admin Dashboard, with a direct link to
  restock it.
- **Sales & Purchase Report** — date-range and type-filtered report with
  transaction totals, built for management to review activity over any
  period.

## Tech Stack

- PHP 8 (PDO with prepared statements throughout — no raw string-built SQL)
- MySQL / MariaDB
- Bootstrap 5 (via CDN)
- Apache (XAMPP)

## Project Structure

```
mapoly_bookshop/
├── auth/              Login, logout
├── books/             Add / edit books
├── categories/         Add / edit / list categories
├── suppliers/          Add / edit / list suppliers
├── transactions/       Record sale, record purchase
├── dashboard/          Admin dashboard, Officer dashboard
├── reports/            Sales & purchase report
├── includes/           Shared DB connection, auth helpers, header/footer
├── assets/css/         Custom styling
└── schema.sql          Full database schema
```

## Setup

1. Copy the `mapoly_bookshop` folder into your XAMPP `htdocs` directory:
   - Windows: `C:\xampp\htdocs\mapoly_bookshop\`
   - Mac: `/Applications/XAMPP/htdocs/mapoly_bookshop/`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Go to `http://localhost/phpmyadmin`, click **Import**, choose
   `schema.sql` from this folder, and click **Go**. This creates the
   `mapoly_bookshop_db` database and all five tables, with a few sample
   books to test with.
4. Create your first admin account: write a short one-time PHP script that
   inserts a row into `tbl_user` using `password_hash()` for the password
   (never insert a plain-text password directly via SQL). Run it once in
   the browser, then **delete the script immediately** — do not leave
   account-creation scripts on the server, and never commit one to version
   control.
5. Log in at `http://localhost/mapoly_bookshop/auth/login.php`.

## Security Notes

- All database queries use PDO prepared statements to prevent SQL
  injection.
- Passwords are stored as bcrypt hashes, never in plain text.
- Every restricted page checks the logged-in user's role server-side via
  `require_role()` in `includes/auth.php` — typing a restricted URL
  directly, without the matching role, returns "Access denied" rather than
  the page content.
- Sale transactions use a database transaction with row locking
  (`SELECT ... FOR UPDATE`) to prevent race conditions from concurrent
  sales.

## Still to Build

- Edit/delete for suppliers' linked books in bulk
- Export reports to PDF/CSV
- Password reset flow for users who forget their password
-