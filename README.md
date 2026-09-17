# MAPOLY Bookshop IMS — Starter Code

This is working scaffolding for Sprints 1–3 of your project (login,
book management, sale/restock transactions, low-stock alerts). It is
not the finished system — it's a correct, running foundation for you
to build the rest on top of (categories/suppliers CRUD, reporting
module, styling, etc.).

## 1. Install the files
Copy the whole `mapoly_bookshop` folder into your XAMPP `htdocs`:
- Windows: `C:\xampp\htdocs\mapoly_bookshop\`
- Mac: `/Applications/XAMPP/htdocs/mapoly_bookshop/`

## 2. Create the database
1. Start Apache and MySQL in the XAMPP Control Panel.
2. Go to `http://localhost/phpmyadmin`.
3. Click **Import**, choose `schema.sql` from this folder, click **Go**.
   This creates the `mapoly_bookshop_db` database, all five tables, and
   a few sample books so you have something to test with.

## 3. Create your first login
1. In your browser, go to `http://localhost/mapoly_bookshop/seed_admin.php`.
2. It will create an admin account: **username `admin1`, password `ChangeMe123!`**.
3. **Delete `seed_admin.php` immediately after** — never leave a script
   like this sitting on a real server.
4. To add a Store Officer account later, either write a similar
   one-off script or build an "Add User" admin page the same way
   `add_book.php` works.

## 4. Log in
Go to `http://localhost/mapoly_bookshop/auth/login.php` and log in with
the admin account above. You'll land on the admin dashboard, which
shows the stock table and any low-stock alerts.

## 5. What's included and where to go next

| File | What it does | Sprint |
|---|---|---|
| `includes/db.php` | Shared PDO database connection | — |
| `includes/auth.php` | `require_login()` / `require_role()` helpers | 1 |
| `auth/login.php`, `auth/logout.php` | Login (Algorithm 0) and logout | 1 |
| `dashboard/admin_dashboard.php` | Stock table + low-stock alerts (Algorithm 3) | 1/3 |
| `dashboard/officer_dashboard.php` | Simple officer landing page | 1 |
| `books/add_book.php` | Add-book form (CRUD create example) | 2 |
| `transactions/record_sale.php` | Sale recording with row-lock + DB transaction (Algorithm 1) | 3 |
| `transactions/record_purchase.php` | Restock recording (Algorithm 2) | 3 |

Still to build, following the same patterns as `add_book.php`:
- **Sprint 2**: edit/delete books, and CRUD for categories and suppliers
- **Sprint 4**: the date-filtered sales/purchase report (query
  `tbl_transaction` with a `WHERE transaction_date BETWEEN ? AND ?`,
  join to `tbl_book` and `tbl_user` for names)
- User management page (admin creates officer accounts through the UI
  instead of `seed_admin.php`)

## 6. Notes on the choices made here
- **PDO with prepared statements** everywhere — never string-concatenate
  user input into SQL.
- **`password_hash()` / `password_verify()`** (bcrypt) for all passwords.
- **`FOR UPDATE` row locking + a real `beginTransaction()/commit()`**
  in the sale/purchase scripts — this is what satisfies NFR5
  (no negative stock from two simultaneous sales).
- **`require_role()`** at the top of every protected page — this is
  what satisfies NFR2 (role-based access enforced server-side, not
  just hidden in the UI).
- Bootstrap is loaded from a CDN, so there's nothing to install for it.
