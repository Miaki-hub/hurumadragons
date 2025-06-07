<?php
// Start session and include database connection
session_start();
require_once 'db_connect.php';

// Initialize variables to avoid undefined variable warnings
$featuredPlayer = [];
$latestNews = [];
$teamStats = [];
$topPlayers = [];

// Fetch featured content from database with error handling
try {
    // Featured Player
    $featuredPlayerStmt = $conn->query("SELECT * FROM players ORDER BY points DESC LIMIT 1");
    $featuredPlayer = $featuredPlayerStmt ? $featuredPlayerStmt->fetch(PDO::FETCH_ASSOC) : [];
    
    // Latest News
    $latestNewsStmt = $conn->query("SELECT * FROM news ORDER BY date DESC LIMIT 3");
    $latestNews = $latestNewsStmt ? $latestNewsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
    // Team Stats - fallback to default values if not found
    $teamStatsStmt = $conn->query("SELECT * FROM teams WHERE id = 1");
    $teamStats = $teamStatsStmt ? $teamStatsStmt->fetch(PDO::FETCH_ASSOC) : [
        'wins' => 0,
        'losses' => 0,
        'points_per_game' => 0.0
    ];
    
    // Top Players
    $topPlayersStmt = $conn->query("SELECT * FROM players ORDER BY points DESC LIMIT 5");
    $topPlayers = $topPlayersStmt ? $topPlayersStmt->fetchAll(PDO::FETCH_ASSOC) : [];
    
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database error: " . $e->getMessage());
    $errorMessage = "We're experiencing technical difficulties. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Huruma Dragons | Elite Basketball</title>
  <meta name="description" content="Official website of Huruma Dragons, Kenya's premier basketball team. Get the latest news, player stats, game schedules, and ticket information.">
  
  <!-- Favicon -->
  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            dragon: {
              red: '#d62828',
              navy: '#003049',
              gold: '#fcbf49',
              dark: '#1a1a1a'
            }
          },
          fontFamily: {
            heading: ['Bebas Neue', 'sans-serif'],
            body: ['Montserrat', 'sans-serif']
          },
          animation: {
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            'float': 'float 6s ease-in-out infinite',
            'fade-in': 'fadeIn 1s ease-in'
          },
          keyframes: {
            float: {
              '0%, 100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-10px)' }
            },
            fadeIn: {
              '0%': { opacity: '0' },
              '100%': { opacity: '1' }
            }
          }
        }
      }
    }
  </script>
  <style type="text/tailwindcss">
    @layer utilities {
      .text-stroke {
        -webkit-text-stroke: 1px theme('colors.dragon.navy');
        text-stroke: 1px theme('colors.dragon.navy');
      }
      .clip-path-hero {
        clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
      }
      .gradient-text {
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        background-image: linear-gradient(to right, theme('colors.dragon.gold'), theme('colors.dragon.red'));
      }
      .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
    }
  </style>
</head>
<body class="font-body bg-dragon-dark text-white">

  <!-- Animated Preloader -->
  <div id="preloader" class="fixed inset-0 z-50 flex items-center justify-center bg-dragon-navy transition-opacity duration-500">
    <div class="animate-bounce flex flex-col items-center">
      <svg class="w-20 h-20 animate-spin-slow text-dragon-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <h2 class="font-heading text-3xl mt-4 text-white">Loading Team Spirit...</h2>
    </div>
  </div>

  <!-- Navigation -->
  <header class="fixed w-full z-40 bg-dragon-navy/90 backdrop-blur-md shadow-lg">
    <div class="container mx-auto px-6 py-3">
      <div class="flex justify-between items-center">
        <a href="index.php" class="flex items-center space-x-3 group">
          <img src="assets/images/logo.png" alt="Huruma Dragons" class="h-12 w-auto transition-transform duration-300 group-hover:scale-110">
          <span class="font-heading text-3xl text-dragon-gold tracking-wider">HURUMA DRAGONS</span>
        </a>
        
        <nav class="hidden lg:block">
          <ul class="flex space-x-8">
            <li><a href="players.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              Team
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-300"></span>
            </a></li>
            <li><a href="#schedule" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              Schedule
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-300"></span>
            </a></li>
            <li><a href="team_stats.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              Stats
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-300"></span>
            </a></li>
            <li><a href="news.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              News
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-300"></span>
            </a></li>
            <li><a href="#tickets" class="px-4 py-2 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full transition-colors duration-300 transform hover:scale-105 shadow-lg">
              Get Tickets
            </a></li>
            <?php if(isset($_SESSION['admin_logged_in'])): ?>
            <li><a href="submit_news.php" class="ml-4 px-4 py-2 bg-dragon-gold text-dragon-navy hover:bg-dragon-red font-bold rounded-full transition-colors duration-300">
              <i class="fas fa-lock mr-2"></i>Admin
            </a></li>
            <?php endif; ?>
          </ul>
        </nav>
        
        <button id="mobile-menu-button" class="lg:hidden text-white focus:outline-none transition-transform duration-300 hover:scale-110">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="lg:hidden hidden bg-dragon-navy/95 px-6 py-4 shadow-xl">
      <ul class="space-y-4">
        <li><a href="players.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300">Team Roster</a></li>
        <li><a href="#schedule" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300">Schedule</a></li>
        <li><a href="team_stats.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300">Team Stats</a></li>
        <li><a href="news.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300">Latest News</a></li>
        <li><a href="#tickets" class="block px-4 py-2 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full text-center transition-colors duration-300 mt-2">
          Get Tickets
        </a></li>
        <?php if(isset($_SESSION['admin_logged_in'])): ?>
        <li><a href="submit_news.php" class="block px-4 py-2 bg-dragon-gold text-dragon-navy hover:bg-dragon-red font-bold rounded-full text-center transition-colors duration-300 mt-2">
          <i class="fas fa-lock mr-2"></i>Admin Panel
        </a></li>
        <?php endif; ?>
      </ul>
    </div>
  </header>

  <!-- Hero Section -->
  <section id="home" class="relative h-screen flex items-center justify-center overflow-hidden clip-path-hero">
    <div class="absolute inset-0 bg-gradient-to-b from-dragon-navy/70 to-dragon-dark/90 z-10"></div>
    <div class="absolute inset-0 bg-[url('assets/images/pattern.png')] opacity-10 z-0"></div>
    <img src="assets/images/hero.jpg" alt="Huruma Dragons Team" class="absolute inset-0 w-full h-full object-cover object-center">
    
    <div class="relative z-20 text-center px-6 container mx-auto animate-fade-in">
      <div class="animate-float">
        <h1 class="font-heading text-6xl md:text-8xl lg:text-9xl mb-6 text-dragon-gold uppercase tracking-tight">
          <span class="text-stroke gradient-text">FEAR THE FIRE</span>
        </h1>
        <p class="text-xl md:text-2xl max-w-2xl mx-auto mb-10 font-semibold text-shadow-lg">
          Kenya's premier basketball team bringing championship intensity to every game
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="news.php" class="px-8 py-4 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full text-lg uppercase tracking-wider transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center">
            <i class="fas fa-play mr-2"></i> Watch Highlights
          </a>
          <a href="players.php" class="px-8 py-4 border-2 border-dragon-gold text-dragon-gold hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full text-lg uppercase tracking-wider transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
            <i class="fas fa-users mr-2"></i> Meet The Team
          </a>
        </div>
      </div>
    </div>
    
    <a href="#stats" class="absolute bottom-10 left-1/2 transform -translate-x-1/2 z-20 animate-bounce">
      <svg class="w-8 h-8 text-dragon-gold hover:text-dragon-red transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
      </svg>
    </a>
  </section>

  <!-- Stats Section -->
  <section id="stats" class="py-20 bg-gradient-to-b from-dragon-dark to-dragon-navy">
    <div class="container mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="font-heading text-4xl md:text-5xl text-dragon-gold mb-4">BY THE NUMBERS</h2>
        <div class="w-20 h-1 bg-dragon-red mx-auto mb-6"></div>
        <p class="text-xl max-w-2xl mx-auto">Our performance this season speaks for itself</p>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="text-center p-6 bg-dragon-dark/50 rounded-xl border border-dragon-navy hover:border-dragon-gold transition-all duration-300 hover:shadow-lg">
          <div class="text-5xl font-bold text-dragon-gold mb-2">
            <?= isset($teamStats['wins']) ? $teamStats['wins'] : '0' ?>-<?= isset($teamStats['losses']) ? $teamStats['losses'] : '0' ?>
          </div>
          <div class="uppercase text-sm tracking-wider">Season Record</div>
        </div>
        <div class="text-center p-6 bg-dragon-dark/50 rounded-xl border border-dragon-navy hover:border-dragon-gold transition-all duration-300 hover:shadow-lg">
          <div class="text-5xl font-bold text-dragon-gold mb-2">
            <?= isset($teamStats['points_per_game']) ? number_format($teamStats['points_per_game'], 1) : '0.0' ?>
          </div>
          <div class="uppercase text-sm tracking-wider">Points Per Game</div>
        </div>
        <div class="text-center p-6 bg-dragon-dark/50 rounded-xl border border-dragon-navy hover:border-dragon-gold transition-all duration-300 hover:shadow-lg">
          <div class="text-5xl font-bold text-dragon-gold mb-2">12</div>
          <div class="uppercase text-sm tracking-wider">Game Win Streak</div>
        </div>
        <div class="text-center p-6 bg-dragon-dark/50 rounded-xl border border-dragon-navy hover:border-dragon-gold transition-all duration-300 hover:shadow-lg">
          <div class="text-5xl font-bold text-dragon-gold mb-2">5</div>
          <div class="uppercase text-sm tracking-wider">All-Star Players</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Top Performers -->
  <section class="py-16 bg-dragon-dark">
    <div class="container mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="font-heading text-4xl text-dragon-gold mb-4">TOP PERFORMERS</h2>
        <div class="w-20 h-1 bg-dragon-red mx-auto"></div>
      </div>
      
      <?php if(!empty($topPlayers)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        <?php foreach($topPlayers as $index => $player): ?>
        <div class="bg-dragon-navy rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-2">
          <div class="relative h-64 bg-gray-800 overflow-hidden">
            <img src="<?= !empty($player['photo']) ? htmlspecialchars($player['photo']) : 'assets/images/player-'.($index+1).'.jpg' ?>" 
                 alt="<?= htmlspecialchars($player['name'] ?? 'Player') ?>" 
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-dragon-navy/90 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-4">
              <h3 class="font-heading text-2xl text-white"><?= htmlspecialchars($player['name'] ?? 'Player') ?></h3>
              <p class="text-dragon-gold text-sm"><?= htmlspecialchars($player['position'] ?? 'Position') ?></p>
            </div>
          </div>
          <div class="p-4">
            <div class="flex justify-between items-center">
              <div>
                <span class="text-dragon-gold font-bold">#<?= htmlspecialchars($player['id'] ?? '00') ?></span>
              </div>
              <div class="text-right">
                <span class="font-bold text-white"><?= $player['points'] ?? 0 ?></span>
                <span class="text-xs text-gray-400">PPG</span>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="text-center py-10">
        <p class="text-xl text-gray-400">Player data coming soon. Check back later!</p>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Next Game Banner -->
  <section id="schedule" class="py-12 bg-dragon-red">
    <div class="container mx-auto px-6">
      <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="mb-6 md:mb-0 text-center md:text-left">
          <h3 class="font-heading text-2xl text-white mb-2">NEXT GAME</h3>
          <h2 class="font-heading text-4xl text-dragon-gold">VS NAIROBI FALCONS</h2>
        </div>
        <div class="text-center md:text-right mb-6 md:mb-0">
          <div class="text-xl font-semibold flex items-center justify-center md:justify-end">
            <i class="fas fa-calendar-alt mr-2 text-dragon-gold"></i> SAT, MAY 11 • 7:00 PM
          </div>
          <div class="text-lg flex items-center justify-center md:justify-end">
            <i class="fas fa-map-marker-alt mr-2 text-dragon-gold"></i> Huruma Arena
          </div>
        </div>
        <a href="#tickets" class="px-8 py-3 bg-dragon-gold hover:bg-white text-dragon-navy font-bold rounded-full uppercase tracking-wider transition-all duration-300 transform hover:scale-105 flex items-center">
          <i class="fas fa-ticket-alt mr-2"></i> Get Tickets
        </a>
      </div>
    </div>
  </section>

  <!-- Featured Player -->
  <section id="team" class="py-20 bg-dragon-dark">
    <div class="container mx-auto px-6">
      <?php if(!empty($featuredPlayer)): ?>
      <div class="flex flex-col lg:flex-row items-center">
        <div class="lg:w-1/2 mb-12 lg:mb-0 lg:pr-12">
          <div class="relative group">
            <div class="absolute -inset-4 bg-dragon-gold/20 rounded-xl transform rotate-1 transition-all duration-500 group-hover:rotate-0"></div>
            <div class="relative z-10 rounded-lg shadow-2xl overflow-hidden">
              <img src="<?= !empty($featuredPlayer['photo']) ? htmlspecialchars($featuredPlayer['photo']) : 'assets/images/player.jpg' ?>" 
                   alt="<?= htmlspecialchars($featuredPlayer['name']) ?>" 
                   class="w-full h-auto transition-transform duration-500 group-hover:scale-105">
            </div>
          </div>
        </div>
        <div class="lg:w-1/2">
          <span class="inline-block px-3 py-1 bg-dragon-navy text-dragon-gold rounded-full text-sm font-bold mb-4">PLAYER OF THE MONTH</span>
          <h2 class="font-heading text-4xl md:text-5xl text-dragon-gold mb-6"><?= htmlspecialchars($featuredPlayer['name']) ?></h2>
          <div class="flex flex-wrap gap-4 mb-8">
            <div class="px-4 py-2 bg-dragon-navy rounded-full flex items-center">
              <span class="font-bold text-dragon-gold mr-2">#<?= htmlspecialchars($featuredPlayer['id']) ?></span>
              <span><?= htmlspecialchars($featuredPlayer['position'] ?? 'Position') ?></span>
            </div>
            <div class="px-4 py-2 bg-dragon-navy rounded-full flex items-center">
              <span class="font-bold text-dragon-gold mr-2"><?= $featuredPlayer['points'] ?? 0 ?></span>
              <span>PPG</span>
            </div>
            <div class="px-4 py-2 bg-dragon-navy rounded-full flex items-center">
              <span class="font-bold text-dragon-gold mr-2"><?= $featuredPlayer['assists'] ?? 0 ?></span>
              <span>APG</span>
            </div>
            <div class="px-4 py-2 bg-dragon-navy rounded-full flex items-center">
              <span class="font-bold text-dragon-gold mr-2"><?= $featuredPlayer['rebounds'] ?? 0 ?></span>
              <span>RPG</span>
            </div>
          </div>
          <p class="text-lg mb-8 leading-relaxed">
            <?= !empty($featuredPlayer['bio']) ? nl2br(htmlspecialchars($featuredPlayer['bio'])) : 'Leading the Dragons with explosive performances. Bio coming soon.' ?>
          </p>
          <a href="players.php#player-<?= $featuredPlayer['id'] ?>" class="inline-flex items-center px-6 py-3 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full transition-colors duration-300">
            Full Player Profile
            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
          </a>
        </div>
      </div>
      <?php else: ?>
      <div class="text-center py-20">
        <h2 class="font-heading text-4xl text-dragon-gold mb-6">FEATURED PLAYER</h2>
        <p class="text-xl text-gray-400">Player of the month will be announced soon!</p>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Latest News Section -->
  <section id="news" class="py-20 bg-dragon-navy">
    <div class="container mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="font-heading text-4xl md:text-5xl text-dragon-gold mb-4">LATEST NEWS</h2>
        <div class="w-20 h-1 bg-dragon-red mx-auto mb-6"></div>
        <p class="text-xl max-w-2xl mx-auto">Stay updated with the latest from Huruma Dragons</p>
      </div>
      
      <?php if(!empty($latestNews)): ?>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach($latestNews as $newsItem): ?>
        <article class="bg-dragon-dark rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 transform hover:-translate-y-2">
          <div class="h-64 bg-gray-800 overflow-hidden relative">
            <img src="assets/images/news-<?= rand(1,3) ?>.jpg" alt="<?= htmlspecialchars($newsItem['title']) ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-dragon-navy/90 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-4">
              <time class="text-sm text-dragon-gold"><?= date('M j, Y', strtotime($newsItem['date'])) ?></time>
            </div>
          </div>
          <div class="p-6">
            <h3 class="font-heading text-2xl text-dragon-gold mb-2"><?= htmlspecialchars($newsItem['title']) ?></h3>
            <p class="text-gray-300 line-clamp-3 mb-4"><?= nl2br(htmlspecialchars(substr($newsItem['content'], 0, 150))) ?>...</p>
            <a href="news.php#news-<?= $newsItem['id'] ?>" class="inline-flex items-center text-dragon-gold hover:text-white font-semibold transition-colors duration-300">
              Read More
              <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
              </svg>
            </a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="text-center py-10">
        <p class="text-xl text-gray-400">No news articles available at the moment. Check back soon for updates!</p>
      </div>
      <?php endif; ?>
      
      <div class="text-center mt-12">
        <a href="news.php" class="px-6 py-3 border border-dragon-gold text-dragon-gold hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full transition-colors duration-300 inline-flex items-center">
          View All News
          <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section class="py-20 bg-gradient-to-r from-dragon-dark to-dragon-navy">
    <div class="container mx-auto px-6 max-w-4xl text-center">
      <div class="bg-dragon-dark/80 rounded-2xl p-10 shadow-2xl border border-dragon-navy transform hover:scale-[1.01] transition-transform duration-300">
        <h2 class="font-heading text-3xl md:text-4xl text-dragon-gold mb-6">JOIN THE DRAGON'S DEN</h2>
        <p class="text-xl mb-8 leading-relaxed">
          Get exclusive updates, behind-the-scenes content, and special offers delivered straight to your inbox. Be the first to know about ticket releases and team news.
        </p>
        <form method="POST" action="subscribe.php" class="flex flex-col sm:flex-row gap-4">
          <input type="email" name="email" placeholder="Your email address" class="flex-grow px-6 py-4 rounded-full bg-dragon-navy/50 border border-dragon-navy focus:border-dragon-gold focus:outline-none text-white placeholder-gray-400" required>
          <button type="submit" class="px-8 py-4 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full uppercase tracking-wider transition-colors duration-300 flex items-center justify-center">
            <i class="fas fa-paper-plane mr-2"></i> Subscribe Now
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- Sponsors -->
  <section class="py-16 bg-dragon-dark">
    <div class="container mx-auto px-6">
      <h3 class="text-center text-gray-400 uppercase text-sm tracking-wider mb-10">PROUDLY SUPPORTED BY</h3>
      <div class="flex flex-wrap justify-center items-center gap-10 md:gap-16">
        <a href="https://sponsor1.com" target="_blank" class="hover:opacity-100 transition-opacity duration-300 hover:scale-110">
          <img src="assets/images/sponsor-1.png" alt="Sponsor" class="h-12 opacity-70">
        </a>
        <a href="https://sponsor2.com" target="_blank" class="hover:opacity-100 transition-opacity duration-300 hover:scale-110">
          <img src="assets/images/sponsor-2.png" alt="Sponsor" class="h-12 opacity-70">
        </a>
        <a href="https://sponsor3.com" target="_blank" class="hover:opacity-100 transition-opacity duration-300 hover:scale-110">
          <img src="assets/images/sponsor-3.png" alt="Sponsor" class="h-12 opacity-70">
        </a>
        <a href="https://sponsor4.com" target="_blank" class="hover:opacity-100 transition-opacity duration-300 hover:scale-110">
          <img src="assets/images/sponsor-4.png" alt="Sponsor" class="h-10 opacity-70">
        </a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-black py-16">
    <div class="container mx-auto px-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
        <div>
          <a href="index.php" class="flex items-center space-x-3 mb-6 group">
            <img src="assets/images/logo.png" alt="Huruma Dragons" class="h-10 transition-transform duration-300 group-hover:scale-110">
            <span class="font-heading text-2xl text-dragon-gold">HURUMA DRAGONS</span>
          </a>
          <p class="text-gray-400 mb-6">
            Kenya's premier basketball organization dedicated to excellence on and off the court.
          </p>
          <div class="flex space-x-4">
            <a href="https://facebook.com/hurumadragons" target="_blank" class="text-gray-400 hover:text-dragon-gold transition-colors duration-300 text-xl">
              <span class="sr-only">Facebook</span>
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/hurumadragons" target="_blank" class="text-gray-400 hover:text-dragon-gold transition-colors duration-300 text-xl">
              <span class="sr-only">Twitter</span>
              <i class="fab fa-twitter"></i>
            </a>
            <a href="https://instagram.com/hurumadragons" target="_blank" class="text-gray-400 hover:text-dragon-gold transition-colors duration-300 text-xl">
              <span class="sr-only">Instagram</span>
              <i class="fab fa-instagram"></i>
            </a>
            <a href="https://youtube.com/hurumadragons" target="_blank" class="text-gray-400 hover:text-dragon-gold transition-colors duration-300 text-xl">
              <span class="sr-only">YouTube</span>
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h3 class="font-heading text-xl text-dragon-gold mb-6 uppercase">Quick Links</h3>
          <ul class="space-y-3">
            <li><a href="players.php" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Team Roster
            </a></li>
            <li><a href="#schedule" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Season Schedule
            </a></li>
            <li><a href="team_stats.php" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Team Stats
            </a></li>
            <li><a href="#tickets" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Ticket Information
            </a></li>
            <li><a href="news.php" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Latest News
            </a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-heading text-xl text-dragon-gold mb-6 uppercase">Organization</h3>
          <ul class="space-y-3">
            <li><a href="#about" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> About Us
            </a></li>
            <li><a href="#front-office" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Front Office
            </a></li>
            <li><a href="#coaches" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Coaching Staff
            </a></li>
            <li><a href="#careers" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Careers
            </a></li>
            <li><a href="#contact" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
              <i class="fas fa-chevron-right text-xs text-dragon-gold mr-2"></i> Contact Us
            </a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="font-heading text-xl text-dragon-gold mb-6 uppercase">Contact Info</h3>
          <address class="not-italic text-gray-400 space-y-3">
            <p class="flex items-start">
              <i class="fas fa-map-marker-alt text-dragon-gold mt-1 mr-3"></i>
              <span>Huruma Arena<br>Nairobi, Kenya</span>
            </p>
            <p class="flex items-center">
              <i class="fas fa-envelope text-dragon-gold mr-3"></i>
              <a href="mailto:info@hurumadragons.co.ke" class="hover:text-dragon-gold">info@hurumadragons.co.ke</a>
            </p>
            <p class="flex items-center">
              <i class="fas fa-phone-alt text-dragon-gold mr-3"></i>
              <a href="tel:+254700000000" class="hover:text-dragon-gold">+254 700 000 000</a>
            </p>
          </address>
        </div>
      </div>
      
      <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500 text-sm">
        <p>&copy; <?= date('Y') ?> Huruma Dragons Basketball Club. All rights reserved. | 
          <a href="#privacy" class="hover:text-dragon-gold">Privacy Policy</a> | 
          <a href="#terms" class="hover:text-dragon-gold">Terms of Service</a>
        </p>
      </div>
    </div>
  </footer>

  <script>
    // Remove preloader when page loads
    window.addEventListener('load', function() {
      const preloader = document.getElementById('preloader');
      preloader.style.opacity = '0';
      setTimeout(() => {
        preloader.style.display = 'none';
      }, 500);
    });

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    mobileMenuButton.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
      mobileMenuButton.classList.toggle('text-dragon-gold');
    });

    // Close mobile menu when clicking on a link
    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach(link => {
      link.addEventListener('click', function() {
        mobileMenu.classList.add('hidden');
        mobileMenuButton.classList.remove('text-dragon-gold');
      });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 100,
            behavior: 'smooth'
          });
        }
      });
    });

    // Add shadow to header on scroll
    window.addEventListener('scroll', function() {
      const header = document.querySelector('header');
      if (window.scrollY > 50) {
        header.classList.add('shadow-xl');
      } else {
        header.classList.remove('shadow-xl');
      }
    });
  </script>
</body>
</html>