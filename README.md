# 🍋 Lemon GDPS

A Geometry Dash Private Server (GDPS) that can be deployed on Render and played immediately.

## 🚀 Quick Deploy on Render

### One-Click Deploy

1. Push this repository to GitHub
2. Go to [Render Dashboard](https://dashboard.render.com)
3. Click **New** → **Blueprint**
4. Connect your GitHub repo
5. Click **Apply** - Render will create both the web service and MySQL database automatically

### Manual Deploy

1. **Create MySQL Database** on Render:
   - Go to **New** → **PostgreSQL/MySQL**
   - Name: `lemon-gdps-db`
   - Copy the connection details

2. **Create Web Service**:
   - Go to **New** → **Web Service**
   - Connect your GitHub repo
   - Runtime: **Docker**
   - Set environment variables:
     ```
     DB_HOST=<your-mysql-host>
     DB_PORT=3306
     DB_NAME=gdps
     DB_USER=root
     DB_PASS=<your-password>
     ADMIN_PASSWORD=<choose-a-strong-password>
     ```
   - Deploy!

3. **Wait for deployment** - the setup script runs automatically on first start.

## 🎮 How to Play

1. Copy your Render server URL (e.g., `lemon-gdps.onrender.com`)
2. Use a GDPS launcher or modify your hosts file to point Geometry Dash to your server
3. Create a new account on the server
4. Start playing!

### For Mobile

Use apps that support custom GD servers:
- **iOS**: iCustoms, Geometry Dash custom server tools
- **Android**: GDPS-specific launchers

## 🔧 Admin Panel

Access the admin panel at `https://your-server.onrender.com/tools/admin.php`

Default password: `admin123` (change via `ADMIN_PASSWORD` env var!)

Features:
- View and manage users
- Rate and feature levels
- Set daily/weekly levels
- Ban/unban users
- Delete levels

## 📋 API Endpoints

The GDPS implements these Geometry Dash API endpoints:

### Account
- `/database/accounts/loginGJAccount.php` - Login
- `/database/accounts/registerGJAccount.php` - Register
- `/database/accounts/syncGJAccount.php` - Sync account data

### Levels
- `/database/gj/getGJLevels21.php` - List levels
- `/database/gj/downloadGJLevel22.php` - Download level
- `/database/gj/uploadGJLevel21.php` - Upload level
- `/database/gj/deleteGJLevelUser21.php` - Delete level

### Social
- `/database/gj/getGJUserInfo19.php` - Get user info
- `/database/gj/updateGJUserScore22.php` - Update score
- `/database/gj/getGJUsers.php` - Search users
- `/database/gj/uploadGJComment21.php` - Post comment
- `/database/gj/getGJComments21.php` - Get comments
- `/database/gj/likeGJItem211.php` - Like
- `/database/gj/rateGJStars211.php` - Rate level
- `/database/gj/rateGJDemon21.php` - Rate difficulty

### Messages & Friends
- `/database/gj/uploadGJMessage20.php` - Send message
- `/database/gj/getGJMessages20.php` - Get messages
- `/database/gj/uploadFriendRequest20.php` - Friend request
- `/database/gj/getGJFriendRequests20.php` - Get requests
- `/database/gj/blockGJUser20.php` - Block user

### Other
- `/database/gj/getGJDailyLevel.php` - Daily level
- `/database/gj/getGJGauntlets21.php` - Gauntlets
- `/database/gj/getGJMapPacks.php` - Map packs
- `/database/gj/getGJSongInfo.php` - Song info
- `/database/gj/getGJSongs21.php` - Song reupload
- `/database/gj/requestUserAccess.php` - Verify access

## 🏗️ Architecture

- **Language**: PHP 8.2
- **Database**: MySQL 8.0
- **Server**: Apache
- **Deployment**: Docker → Render

## 📁 Project Structure

```
├── accounts/          # Legacy account endpoints
├── assets/            # CSS and static files
├── database/
│   ├── accounts/      # Game account endpoints (login/register)
│   └── gj/            # Game data endpoints (levels, comments, etc.)
├── incl/              # Include files (database, library)
├── tools/             # Admin panel and setup
├── .htaccess          # Apache URL rewriting
├── config.php         # Configuration
├── Dockerfile         # Docker image definition
├── docker-entrypoint.sh
├── index.php          # Landing page
└── render.yaml        # Render blueprint
```

## 🔒 Security Notes

- Change the default `ADMIN_PASSWORD` environment variable
- Use HTTPS (Render provides this automatically)
- The database setup runs automatically on first deploy
- Player passwords are hashed with PHP's password_hash()

## 📝 License

This is a GDPS implementation for educational purposes.
Geometry Dash is owned by RobTop Games.
