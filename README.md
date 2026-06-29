# MyRecordingStudio — Studio Booking System

A web application for managing a chain of recording studios: clients can browse
locations, check real-time studio availability, and book sessions, while
administrators manage locations, oversee all bookings, and book on behalf of
clients.

Built with **plain PHP and MySQL** (no framework), the project demonstrates a
clean separation of concerns, parameterized SQL, secure authentication, and
role-based access control.

> Originally developed as a university final project (ISIT307 — Web Server
> Programming). Cleaned up and documented for public reference.

---

## Features

### Authentication & roles
- User registration and login with **bcrypt-hashed passwords** (`password_hash`).
- Session-based authentication with two roles: **client** and **administrator**.
- Route guards (`requireLogin`, `requireAdmin`) protect every page by role.

### Locations (studios)
- Browse all locations with description, number of studios, and hourly cost.
- **Search** locations by ID or description.
- Filter by **available now** (studios free at the current time) — computed live
  from active bookings.
- Admin-only: **create / edit** locations and view **fully booked** locations.

### Bookings
- Create a booking with validation for:
  - Date not in the past.
  - Opening hours (10:00–22:00, configurable).
  - Duration between 1 and 12 hours.
  - **Overlap detection** — a slot is rejected when all studios at that location
    are already booked for the requested time window.
- **Edit** and **cancel** bookings (only before the session has started).
- View **upcoming** and **completed / cancelled** sessions (booking history).
- Automatic **total cost** calculation (hourly rate × duration).
- Admins can create bookings **on behalf of any client**.

### Clients (admin)
- List all registered clients.
- **Search** clients by ID, name, email, or phone.
- View clients **currently in a studio** ("active now").

---

## Tech stack

| Layer        | Technology                                  |
|--------------|---------------------------------------------|
| Language     | PHP 7.4+ (`mysqli`, prepared statements)    |
| Database     | MySQL / MariaDB                              |
| Frontend     | Server-rendered HTML + vanilla CSS          |
| Auth         | PHP sessions + `password_hash` / `password_verify` |
| Server       | Apache (XAMPP / LAMP) or PHP built-in server |

### Security practices
- All queries use **prepared statements** with bound parameters (no string
  concatenation of user input).
- Passwords are stored as **bcrypt hashes**, never in plain text.
- All dynamic output is escaped with `htmlspecialchars` to prevent XSS.
- Role checks are enforced server-side on every protected page.

---

## Project structure

```
.
├── index.php                # Entry point — redirects based on auth state
├── config/
│   └── database.php         # DB connection + studio opening hours
├── classes/
│   ├── User.php             # Registration, login, client queries
│   ├── Location.php         # Location CRUD, availability queries
│   └── Booking.php          # Booking creation, validation, availability
├── includes/
│   ├── auth.php             # Session helpers & route guards
│   ├── header.php           # Shared navbar / page head
│   └── footer.php           # Shared footer
├── pages/
│   ├── auth/                # login, register, logout
│   ├── locations/           # list, search, create, edit
│   ├── bookings/            # create, edit, cancel, list, history
│   └── clients/             # list, search (admin)
├── assets/css/
│   └── style.css            # Application styles
└── sql/
    └── myrecordingstudio.sql # Schema + sample data
```

### Data model

```
users (id, name, phone, email, password, type, created_at)
   │
   │ 1───N
   ▼
bookings (id, user_id, location_id, booking_date, start_time, duration, status, created_at)
   ▲
   │ N───1
   │
locations (id, description, num_studios, cost_per_hour, created_at)
```

---

## Getting started

### Prerequisites
- PHP 7.4 or newer
- MySQL or MariaDB
- A web server (Apache via [XAMPP](https://www.apachefriends.org/), or PHP's
  built-in server)

### 1. Clone the repository
```bash
git clone https://github.com/<your-username>/<repo-name>.git
cd <repo-name>
```

### 2. Create the database
Import the schema and sample data:
```bash
mysql -u root -p < sql/myrecordingstudio.sql
```
This creates the `myrecordingstudio` database, all tables, and demo records.

### 3. Configure the connection
Edit [config/database.php](config/database.php) if your credentials differ from
the defaults:
```php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'myrecordingstudio';
```
You can also adjust the studio opening hours here (`STUDIO_OPEN_HOUR`,
`STUDIO_CLOSE_HOUR`).

### 4. Run the app

**Option A — XAMPP / Apache:** place the project in `htdocs/` and open
`http://localhost/<project-folder>/`.

**Option B — PHP built-in server:**
```bash
php -S localhost:8000
```
Then open `http://localhost:8000`.

---

## Demo accounts

The sample data in [sql/myrecordingstudio.sql](sql/myrecordingstudio.sql)
includes ready-to-use accounts:

| Role           | Email             | Password   |
|----------------|-------------------|------------|
| Administrator  | admin@test.com    | admin123   |
| Client         | john@test.com     | john123    |
| Client         | maria@test.com    | maria123   |
| Client         | carlos@test.com   | carlos123  |

> These are demo credentials for local testing only.

---

## Possible improvements

Ideas for extending the project (not implemented in this version):

- CSRF tokens on state-changing forms.
- Environment-based configuration (`.env`) instead of hardcoded DB credentials.
- Pagination for large client / booking lists.
- A composer autoloader and PSR-4 namespaces.
- Unit tests for the booking availability logic.
