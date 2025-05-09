<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reclamation Options</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* NEW BANNER STYLES */
        .top-banner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            background-color: #FFFFFF; /* Changed from #C1FFC1 to white */
            width: 100%;
            box-sizing: border-box;
            position: relative;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); /* Added subtle shadow for depth */
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
        }
        
        .logo-img {
            height: 50px;
            transition: transform 0.3s;
        }
        
        .logo-container:hover .logo-img {
            transform: scale(1.05);
        }
        
        .logo-text {
            font-family: 'Arial Black', sans-serif;
            font-size: 22px;
        }
        
        .logo-text span:first-child {
            color: #3498db;
        }
        
        .logo-text span:last-child {
            color: #333;
        }
        /* Add these new styles */
                .search-container {
                    position: relative;
                }
                
                .search-suggestions {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    width: 100%;
                    background: white;
                    border-radius: 5px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    display: none;
                    z-index: 1000;
                }
                
                .suggestion-item {
                    padding: 12px 20px;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .suggestion-item:hover {
                    background: #f5f5f5;
                    color: #3498db;
                }
                
                .suggestion-icon {
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
        
        /* Update existing search bar styles */
        .search-bar {
            position: relative;
            display: flex;
            width: 300px;
        }
        
        .search-bar input {
            flex-grow: 1;
            padding: 8px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 20px;
            font-family: 'Montserrat', sans-serif;
            background-color: rgba(255,255,255,0.5);
            backdrop-filter: blur(5px);
            outline: none;
            transition: all 0.3s;
        }
        
        .search-bar input:focus {
            background-color: rgba(255,255,255,0.8);
            border-color: #3498db;
        }
        
        .search-bar button {
            background: none;
            color: #3498db;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            position: absolute;
            right: 40px;
        }
        
        .menu-button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }
        
        .menu-button img {
            height: 30px;
            transition: transform 0.3s;
        }
        
        .menu-button.active img {
            transform: rotate(90deg);
        }
        
        .menu-dropdown {
            position: absolute;
            right: 30px;
            top: 60px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 200px;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
        }
        
        .menu-button.active .menu-dropdown {
            max-height: 300px;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: block;
            color: #333;
            text-decoration: none;
            font-family: 'Montserrat', sans-serif;
            border-bottom: 1px solid #eee;
            transition: all 0.2s;
        }
        
        .menu-item:hover {
            background: #f5f5f5;
            color: #3498db;
        }

        /* Rest of your existing styles */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .spinner {
            display: flex;
            gap: 10px;
        }
        
        .dot {
            width: 20px;
            height: 20px;
            background: #4CAF50;
            border-radius: 50%;
            animation: bounce 1.5s infinite ease-in-out;
        }
        
        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-out {
            opacity: 0;
        }
        
        .hero-video-container {
            position: relative;
            width: 100%;
            height: 60vh;
            overflow: hidden;
        }
        
        .hero-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Update video overlay with gradient background */
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;

        }
        
        .header {
            text-align: center;
            padding: 30px 0;
        }
        
        .header h1 {
            font-size: 42px;
            color: #C1FFC1; /* Changed to match banner */
            margin-bottom: 10px;
        }
        
        .header h2 {
            font-size: 24px;
            color: #FFFFFF; /* Changed to white */
            font-weight: 500;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2); /* Added subtle shadow for better readability */
        }
        
        .options-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            padding: 20px;
            flex-wrap: wrap;
        }
        
        .option-card {
            width: 280px;
            height: 400px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .option-card:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .card-content {
            padding: 20px;
            text-align: center;
        }
        
        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        
        .card-description {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="spinner">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <!-- NEW TOP BANNER -->
    <div class="top-banner">
        <div class="logo-container" onclick="window.location.href='../home.php'">
            <img src="hotelia.png" alt="Hotelia Logo" class="logo-img">
            <div class="logo-text">
                <span>Hotelia</span><span>Smart</span>
            </div>
        </div>
        
        <!-- Update the search container HTML -->
        <div class="search-container">
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search...">
                <button></button>
            </div>
            <div class="search-suggestions" id="searchSuggestions">
                <div class="suggestion-item" data-url="../home.php">
                    <span class="suggestion-icon">🏠</span>
                    Home Page
                </div>
                <div class="suggestion-item" data-url="chambre.php">
                    <span class="suggestion-icon">🛏️</span>
                    Chambre Reclamation
                </div>
            </div>
        </div>

        <!-- Add this JavaScript before the closing body tag -->
        <script>
            const searchInput = document.getElementById('searchInput');
            const searchSuggestions = document.getElementById('searchSuggestions');
            
            // Show suggestions when focusing on search input
            searchInput.addEventListener('focus', () => {
                searchSuggestions.style.display = 'block';
            });
            
            // Filter suggestions based on input
            searchInput.addEventListener('input', () => {
                const value = searchInput.value.toLowerCase();
                const items = searchSuggestions.getElementsByClassName('suggestion-item');
                
                Array.from(items).forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(value)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
                
                if (value) {
                    searchSuggestions.style.display = 'block';
                }
            });
            
            // Handle suggestion clicks
            document.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    const url = item.getAttribute('data-url');
                    navigateTo(url);
                });
            });
            
            // Close suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.search-container')) {
                    searchSuggestions.style.display = 'none';
                }
            });
        </script>
        
        <div class="menu-container">
            <button class="menu-button" id="menuButton">
                <img src="menu.png" alt="Menu">
            </button>
            <div class="menu-dropdown">
                <a href="../frontoffice/home.php" class="menu-item">Home</a>
                <a href="#" class="menu-item">My Account</a>
                <a href="#" class="menu-item">Settings</a>
                <a href="#" class="menu-item">Logout</a>
            </div>
        </div>
    </div>

    <!-- Video Section with Overlay -->
    <div class="hero-video-container">
        <video class="hero-video" autoplay muted loop>
            <source src="hoteliaa.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="video-overlay">
            <!-- Removed the img element -->
        </div>
    </div>

    <div class="header">
        <h1>HOTELIA SMART</h1>
        <h2>choisir votre reclamation</h2>
    </div>

    <div class="options-container">
        <div class="option-card" onclick="navigateTo('chambre.php')">
            <img src="room.jpg" alt="Chambres" class="card-image">
            <div class="card-content">
                <h3 class="card-title">Chambres</h3>
                <p class="card-description">Problèmes de propreté, équipement manquant, climatisation, etc.</p>
            </div>
        </div>
        
        <div class="option-card" onclick="navigateTo('restaurant.php')">
            <img src="restaurant.jpg" alt="Restaurant" class="card-image">
            <div class="card-content">
                <h3 class="card-title">Restaurant</h3>
                <p class="card-description">Qualité de la nourriture, service, réservations, etc.</p>
            </div>
        </div>
        
        <div class="option-card" onclick="navigateTo('piscine.php')">
            <img src="pool.jpg" alt="Piscine" class="card-image">
            <div class="card-content">
                <h3 class="card-title">Piscine</h3>
                <p class="card-description">Propreté, sécurité, température de l'eau, etc.</p>
            </div>
        </div>

        <!-- New Feedback Card -->
        <div class="option-card feedback-card" onclick="navigateTo('../feedback/feedback.php')">
            <img src="feedback.jpeg" alt="Feedback" class="card-image">
            <div class="card-content">
                <h3 class="card-title">Share Your Feedback</h3>
                <p class="card-description">Help us improve by sharing your experience with our services.</p>
            </div>
            <div class="feedback-badge">
                <i class="fas fa-star"></i>
            </div>
        </div>
    </div>

    <!-- Add these styles to your existing style section -->
    <style>
        /* ... existing styles ... */
        
        .feedback-card {
            border: 2px solid #3498db;
            position: relative;
            overflow: visible;
        }

        .feedback-badge {
            position: absolute;
            top: -15px;
            right: -15px;
            background: #3498db;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .feedback-card:hover .feedback-badge {
            transform: rotate(360deg) scale(1.1);
        }
    </style>

    <!-- Add Font Awesome to the head section if not already present -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        // Preloader functionality
        window.addEventListener('load', function() {
            const preloader = document.querySelector('.preloader');
            
            // Fade out preloader when page is loaded
            setTimeout(function() {
                preloader.classList.add('fade-out');
                
                // Remove preloader from DOM after animation completes
                setTimeout(function() {
                    preloader.style.display = 'none';
                }, 500);
            }, 1500);
        });

        // Menu toggle functionality
        const menuButton = document.getElementById('menuButton');
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });

        // Close menu when clicking anywhere else
        document.addEventListener('click', function() {
            menuButton.classList.remove('active');
        });

        // Prevent menu from closing when clicking inside it
        const menuDropdown = document.querySelector('.menu-dropdown');
        if (menuDropdown) {
            menuDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        function navigateTo(url) {
            const preloader = document.querySelector('.preloader');
            preloader.style.display = 'flex';
            preloader.classList.remove('fade-out');
            
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        }
    </script>
</body>
</html>