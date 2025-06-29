# Setup Guide - Installing PHP and Testing Battle Pass Module

Since PHP is not currently installed on your Mac, here are several options to get your battlepass module running locally:

## Option 1: Install PHP with Homebrew (Recommended)

### Step 1: Install Homebrew (if not installed)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

### Step 2: Install PHP
```bash
brew install php
```

### Step 3: Verify Installation
```bash
php --version
```

### Step 4: Start Local Server
```bash
cd /Users/levankotolashvili/Desktop/geostrike-battlepass
php -S localhost:8000
```

### Step 5: Test in Browser
Open: `http://localhost:8000/test_battlepass.php`

## Option 2: Install XAMPP (All-in-One Solution)

### Step 1: Download XAMPP
- Visit: https://www.apachefriends.org/download.html
- Download XAMPP for macOS
- Install the .dmg file

### Step 2: Start XAMPP
- Open XAMPP Control Panel
- Start Apache and MySQL

### Step 3: Copy Files
```bash
cp -r /Users/levankotolashvili/Desktop/geostrike-battlepass /Applications/XAMPP/htdocs/
```

### Step 4: Test in Browser
Open: `http://localhost/geostrike-battlepass/test_battlepass.php`

## Option 3: Use Docker (Advanced)

### Create Dockerfile
```dockerfile
FROM php:8.1-apache
COPY . /var/www/html/
EXPOSE 80
```

### Run Docker Container
```bash
cd /Users/levankotolashvili/Desktop/geostrike-battlepass
docker build -t battlepass-test .
docker run -p 8080:80 battlepass-test
```

### Test in Browser
Open: `http://localhost:8080/test_battlepass.php`

## Option 4: Online Testing (Quick & Easy)

### Use PHP Playground
1. Copy the content of `test_battlepass.php`
2. Visit: https://3v4l.org/ or https://onecompiler.com/php
3. Paste the code and run it

**Note**: Styling won't work perfectly in online playgrounds, but you can test the PHP logic.

## After Setup - What You'll See

### ✅ Working Battle Pass Interface
- **Header**: Level 5, 520/600 EXP, Season timer
- **Left Panel**: Georgian missions with progress bars
- **Right Panel**: TOP 10 leaderboard
- **Bottom**: Horizontal rewards track (13 levels)

### 🎯 Features to Test
1. **Responsive Design**: Resize browser window
2. **Mission Progress**: Different completion percentages
3. **Leaderboard**: Player ranking with avatars
4. **Reward States**: Earned, current, locked levels
5. **Georgian Text**: Headers and labels in Georgian

### 🔧 Customize Test Data
Edit `test_battlepass.php` to modify:
- User level and EXP
- Mission descriptions and progress
- Leaderboard players
- Reward items and states

## Troubleshooting

### PHP Installation Issues
```bash
# Check if PHP is in PATH
echo $PATH

# Manual PHP path (if installed)
/usr/bin/php --version
/opt/homebrew/bin/php --version
```

### Port Already in Use
```bash
# Try different port
php -S localhost:8001

# Kill process using port 8000
lsof -ti:8000 | xargs kill -9
```

### Permission Issues
```bash
# Fix file permissions
chmod -R 755 /Users/levankotolashvili/Desktop/geostrike-battlepass
```

### CSS Not Loading
- Ensure `stylesheet.css` exists in project root
- Check browser console for 404 errors
- Verify file paths are correct

## Next Steps

Once you have PHP running and can see the Battle Pass interface:

1. **Integrate with your main application**
2. **Connect to real database**
3. **Add actual user authentication**
4. **Implement mission tracking logic**
5. **Add reward claiming functionality**

## Quick Start Command

Once PHP is installed, run this single command:
```bash
cd /Users/levankotolashvili/Desktop/geostrike-battlepass && php -S localhost:8000
```

Then open: `http://localhost:8000/test_battlepass.php` 