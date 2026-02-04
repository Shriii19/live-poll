# Live Poll Platform

Real-Time Live Poll Platform with IP Restriction & Admin Moderation

## Features

- ✅ **Authentication**: Basic login system (only authenticated users can access polls)
- ✅ **IP-Restricted Voting**: One vote per IP per poll (Core PHP logic)
- ✅ **Real-Time Results**: Updates every 1 second without page reload (AJAX)
- ✅ **Admin Dashboard**: View voters, release IPs, manage polls
- ✅ **Vote History**: Complete audit trail of votes and releases
- ✅ **No Page Reloads**: All interactions via AJAX

## Technology Stack

- **Backend**: PHP 8.x (Pure PHP + Core PHP VotingEngine)
- **Database**: MySQL
- **Frontend**: HTML, CSS, Bootstrap 5, JavaScript, jQuery
- **Real-time**: AJAX polling

## Quick Setup

### 1. Prerequisites

- PHP 8.x with PDO MySQL extension
- MySQL Server running

### 2. Database Setup

First, update database credentials in `config.php` if needed:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'live_poll');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Then run the setup script:

```bash
cd live-poll
php setup_database.php
```

### 3. Start the Server

```bash
cd C:\Users\mudab\Desktop\live-poll\public
php -S localhost:8000
```

### 4. Access the Application

Open browser: http://localhost:8000

**Demo Accounts:**
- Admin: `admin@poll.com` / `password`
- User: `user@poll.com` / `password`

## Project Structure

```
live-poll/
├── app/
│   └── CorePHP/
│       └── VotingEngine.php    # Core PHP voting logic (MANDATORY)
├── config.php                   # Database & session config
├── setup_database.php           # Database setup script
└── public/
    ├── index.php                # Main router
    ├── api/
    │   ├── auth.php             # Login API
    │   ├── polls.php            # Polls API
    │   ├── vote.php             # Voting API
    │   └── admin.php            # Admin API
    ├── auth/
    │   └── login.php            # Login page
    └── pages/
        ├── polls.php            # User polls page
        └── admin.php            # Admin dashboard
```

## Core PHP VotingEngine

Located at `app/CorePHP/VotingEngine.php`, this handles:

- **IP Validation**: Validates IPv4 and IPv6 addresses
- **Vote Restriction**: Enforces one vote per IP per poll
- **Vote Casting**: Records votes with IP tracking
- **Vote Release**: Admin can release IP to allow re-voting
- **Vote History**: Maintains complete audit trail

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /login | User login |
| POST | /logout | User logout |
| GET | /api/polls | List active polls |
| GET | /api/polls/{id} | Get single poll |
| POST | /api/vote | Submit vote |
| GET | /api/polls/{id}/results | Get live results |
| GET | /api/admin/polls | Admin: list all polls |
| GET | /api/admin/polls/{id}/voters-history | Admin: get voters with history |
| POST | /api/admin/release-ip | Admin: release IP vote |

## Modules Implemented

### Module 1: Authentication & Poll Display ✅
- Basic login authentication
- Poll listing and viewing
- AJAX navigation without page reload

### Module 2: IP-Restricted Voting ✅
- Core PHP VotingEngine
- One vote per IP per poll
- IP validation and tracking
- Vote submission via AJAX

### Module 3: Real-Time Poll Results ✅
- Results update every 1 second
- No page refresh needed
- Live vote counts and percentages

### Module 4: IP Release & Vote Rollback ✅
- Admin can view all voters per poll
- Release IP functionality (removes vote)
- Complete vote history preserved
- Re-voting allowed after release
