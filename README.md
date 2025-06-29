# Battle Pass Module Testing Guide

## Prerequisites

1. **PHP 7.4+** with a local server
2. **Web browser** 
3. **Local development environment** (XAMPP, MAMP, WAMP, or built-in PHP server)

## Quick Start - Local Testing

### Method 1: Using PHP Built-in Server (Easiest)

1. **Open terminal/command prompt** in the project directory:
   ```bash
   cd /Users/levankotolashvili/Desktop/geostrike-battlepass
   ```

2. **Start PHP built-in server**:
   ```bash
   php -S localhost:8000
   ```

3. **Choose a test file** and open in browser:
   - **Simple Components Test**: `http://localhost:8000/simple_test.php` (Recommended first)
   - **Full Controller Test**: `http://localhost:8000/test_battlepass.php`

### Method 2: Using XAMPP/MAMP/WAMP

1. **Copy project folder** to your web server directory:
   - XAMPP: `C:\xampp\htdocs\geostrike-battlepass\` (Windows) or `/Applications/XAMPP/htdocs/geostrike-battlepass/` (Mac)
   - MAMP: `/Applications/MAMP/htdocs/geostrike-battlepass/`
   - WAMP: `C:\wamp64\www\geostrike-battlepass\`

2. **Start your server** (Apache/PHP)

3. **Choose a test file** and open in browser:
   - **Simple Components Test**: `http://localhost/geostrike-battlepass/simple_test.php` (Recommended first)
   - **Full Controller Test**: `http://localhost/geostrike-battlepass/test_battlepass.php`

## Testing Different Scenarios

### Two Test Files Available

1. **`simple_test.php`** - Tests individual components (Recommended first)
   - Loads components directly without CoreController
   - Faster and easier to debug
   - Shows pure Battle Pass design
   - Use this if you get errors with the full test

2. **`test_battlepass.php`** - Tests full integration with CoreController
   - More complete simulation of real usage
   - Tests all dependencies and initialization
   - Use this after `simple_test.php` works

### Test Authenticated User
Both files simulate a logged-in user and show the full Battle Pass interface.

### Test Unauthenticated User
To test the login prompt, modify either test file:
```php
// Comment out this line:
// $_SESSION['steamid64'] = '76561198148128745';
```

### Test Different Data
Modify the mock data arrays in either test file to test:
- Different user levels
- Various mission progress
- Different reward states
- Custom leaderboard data

## File Structure

```
geostrike-battlepass/
├── battlepass/
│   ├── ext/
│   │   ├── CoreController.php          # Main controller
│   │   ├── components/                 # UI components
│   │   ├── models/                    # Data models
│   │   └── helpers/                   # Helper classes
│   ├── style/
│   │   └── 1.css                      # Battle Pass styles
│   └── forward/                       # Entry points
├── test_battlepass.php                # Full integration test
├── simple_test.php                    # Simple component test  
├── stylesheet.css                     # Main site styles
└── README.md                          # This file
```

## Features Tested

✅ **Header Section**: Level display, season info, user stats  
✅ **Missions Section**: Progress tracking, rewards, timers  
✅ **Leaderboard**: TOP 10 players with avatars and EXP  
✅ **Rewards Track**: 13 levels with earned/current/locked states  
✅ **Responsive Design**: Works on all screen sizes  
✅ **Site Branding**: Uses CSS variables from main stylesheet  

## Troubleshooting

### PHP Errors
- Ensure PHP 7.4+ is installed
- Check file permissions
- Enable error reporting in PHP

### Styling Issues
- Verify `stylesheet.css` and `battlepass/style/1.css` are loading
- Check CSS variable definitions
- Clear browser cache

### Missing Images
- Ensure `avatar.jpg` exists in the root directory
- Update image paths in the test data

## Integration with Main Application

To integrate with your main application:

1. **Include the CSS** in your main layout:
   ```html
   <link rel="stylesheet" type="text/css" href="battlepass/style/1.css">
   ```

2. **Initialize the controller**:
   ```php
   require_once 'battlepass/ext/CoreController.php';
   $battlepass = new CoreController($Db, $General, $Translate, $Modules, $server_group);
   ```

3. **Render the battle pass**:
   ```php
   echo $battlepass->renderBattlePass($nameseason, $currentLevel, $explvl, ...);
   ```

## Development Notes

- The module uses Georgian language for headers and labels
- All styling follows the site's existing CSS variable system
- Components are modular and can be used independently
- The design matches the provided Battle Pass reference image 