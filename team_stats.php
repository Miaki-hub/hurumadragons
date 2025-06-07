<?php
include 'db_connect.php';

// Fetch team statistics with error handling
try {
    // Fetch team stats with default values if columns don't exist
    $teamStmt = $conn->prepare("
        SELECT 
            COALESCE(wins, 0) as wins,
            COALESCE(losses, 0) as losses,
            COALESCE(points_per_game, 0) as points_per_game,
            COALESCE(rebounds_per_game, 0) as rebounds_per_game,
            COALESCE(assists_per_game, 0) as assists_per_game
        FROM teams 
        WHERE id = 1
    ");
    $teamStmt->execute();
    $team = $teamStmt->fetch(PDO::FETCH_ASSOC) ?: [
        'wins' => 0,
        'losses' => 0,
        'points_per_game' => 0,
        'rebounds_per_game' => 0,
        'assists_per_game' => 0
    ];

    // Fetch top scorers
    $playersStmt = $conn->prepare("
        SELECT id, name, points, photo 
        FROM players 
        ORDER BY points DESC 
        LIMIT 5
    ");
    $playersStmt->execute();
    $topScorers = $playersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $team = [
        'wins' => 0,
        'losses' => 0,
        'points_per_game' => 0,
        'rebounds_per_game' => 0,
        'assists_per_game' => 0
    ];
    $topScorers = [];
}

// Calculate win percentage
$totalGames = $team['wins'] + $team['losses'];
$winPercentage = $totalGames > 0 ? round(($team['wins'] / $totalGames) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Statistics | Huruma Dragons</title>
  <meta name="description" content="Detailed statistics and performance metrics for Huruma Dragons basketball team">
  
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
            'fade-in': 'fadeIn 1s ease-in'
          },
          keyframes: {
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
      .gradient-text {
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        background-image: linear-gradient(to right, theme('colors.dragon.gold'), theme('colors.dragon.red'));
      }
      .stat-card {
        transition: all 0.3s ease;
      }
      .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      }
      .result-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
      }
      .win {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
      }
      .loss {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
      }
      .upcoming {
        background-color: rgba(23, 162, 184, 0.2);
        color: #17a2b8;
      }
    }
  </style>
</head>
<body class="font-body bg-dragon-dark text-white">

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
            <li><a href="schedule.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
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
        <li><a href="schedule.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300">Schedule</a></li>
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

  <!-- Main Content -->
  <section class="pt-32 pb-20 bg-gradient-to-b from-dragon-dark to-dragon-navy">
    <div class="container mx-auto px-6">
      <div class="text-center mb-12">
        <h1 class="font-heading text-4xl md:text-5xl text-dragon-gold mb-4 animate-fade-in">TEAM STATISTICS</h1>
        <div class="w-20 h-1 bg-dragon-red mx-auto mb-6"></div>
        <p class="text-xl max-w-2xl mx-auto text-gray-300">Season performance metrics and player statistics</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Team Overview Card -->
        <div class="stat-card bg-dragon-dark/50 rounded-xl p-8 border border-dragon-navy hover:border-dragon-gold transition-all duration-300">
          <h2 class="font-heading text-3xl text-dragon-gold mb-6 flex items-center">
            <i class="fas fa-users mr-3"></i> TEAM OVERVIEW
          </h2>
          
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-dragon-navy/80 p-4 rounded-lg text-center border border-dragon-navy hover:border-dragon-gold transition-colors duration-300">
              <div class="text-4xl font-bold text-dragon-gold mb-2"><?= $team['wins'] ?></div>
              <div class="text-gray-300 uppercase text-sm tracking-wider">Wins</div>
            </div>
            <div class="bg-dragon-navy/80 p-4 rounded-lg text-center border border-dragon-navy hover:border-dragon-gold transition-colors duration-300">
              <div class="text-4xl font-bold text-dragon-gold mb-2"><?= $team['losses'] ?></div>
              <div class="text-gray-300 uppercase text-sm tracking-wider">Losses</div>
            </div>
            <div class="bg-dragon-navy/80 p-4 rounded-lg text-center border border-dragon-navy hover:border-dragon-gold transition-colors duration-300">
              <div class="text-4xl font-bold text-dragon-gold mb-2"><?= $winPercentage ?>%</div>
              <div class="text-gray-300 uppercase text-sm tracking-wider">Win Rate</div>
            </div>
          </div>
          
          <div class="mb-6">
            <h3 class="font-semibold text-dragon-gold mb-3 flex items-center">
              <i class="fas fa-chart-line mr-2"></i> WINNING PROGRESSION
            </h3>
            <div class="w-full bg-dragon-navy rounded-full h-3">
              <div class="bg-gradient-to-r from-dragon-gold to-dragon-red h-3 rounded-full" 
                   style="width: <?= $winPercentage ?>%"></div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-dragon-navy/50 p-3 rounded-lg border border-dragon-navy">
              <div class="text-2xl font-bold text-dragon-gold"><?= number_format($team['points_per_game'], 1) ?></div>
              <div class="text-gray-300 text-sm">Points Per Game</div>
            </div>
            <div class="bg-dragon-navy/50 p-3 rounded-lg border border-dragon-navy">
              <div class="text-2xl font-bold text-dragon-gold"><?= number_format($team['rebounds_per_game'], 1) ?></div>
              <div class="text-gray-300 text-sm">Rebounds Per Game</div>
            </div>
            <div class="bg-dragon-navy/50 p-3 rounded-lg border border-dragon-navy">
              <div class="text-2xl font-bold text-dragon-gold"><?= number_format($team['assists_per_game'], 1) ?></div>
              <div class="text-gray-300 text-sm">Assists Per Game</div>
            </div>
          </div>
        </div>
        
        <!-- Top Scorers Card -->
        <div class="stat-card bg-dragon-dark/50 rounded-xl p-8 border border-dragon-navy hover:border-dragon-gold transition-all duration-300">
          <h2 class="font-heading text-3xl text-dragon-gold mb-6 flex items-center">
            <i class="fas fa-basketball-ball mr-3"></i> TOP PERFORMERS
          </h2>
          
          <div class="space-y-4">
            <?php if (!empty($topScorers)): ?>
              <?php foreach ($topScorers as $index => $player): ?>
                <a href="player_profile.php?id=<?= $player['id'] ?>" class="group block">
                  <div class="flex items-center bg-dragon-navy/80 p-4 rounded-lg border border-dragon-navy hover:border-dragon-gold transition-colors duration-300">
                    <div class="w-12 h-12 rounded-full overflow-hidden mr-4 border-2 border-dragon-gold">
                      <?php if (!empty($player['photo'])): ?>
                        <img src="<?= htmlspecialchars($player['photo']) ?>" 
                             alt="<?= htmlspecialchars($player['name']) ?>" 
                             class="w-full h-full object-cover">
                      <?php else: ?>
                        <div class="w-full h-full bg-dragon-gold text-dragon-navy flex items-center justify-center">
                          <i class="fas fa-user text-xl"></i>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="flex-grow">
                      <h3 class="font-semibold group-hover:text-dragon-gold transition-colors duration-300">
                        <?= htmlspecialchars($player['name']) ?>
                      </h3>
                      <div class="text-sm text-gray-400">Points Leader</div>
                    </div>
                    <div class="text-2xl font-bold text-dragon-gold"><?= $player['points'] ?? 0 ?></div>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-8 text-gray-400">
                <i class="fas fa-user-slash text-4xl mb-4"></i>
                <p>Player statistics coming soon</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <!-- Season Performance Section -->
      <div class="stat-card bg-dragon-dark/50 rounded-xl p-8 border border-dragon-navy hover:border-dragon-gold transition-all duration-300 mb-12">
        <h2 class="font-heading text-3xl text-dragon-gold mb-6 flex items-center">
          <i class="fas fa-calendar-alt mr-3"></i> SEASON PERFORMANCE
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Win/Loss Chart Placeholder -->
          <div>
            <div class="bg-dragon-navy/50 rounded-lg p-4 h-64 flex items-center justify-center border border-dragon-navy">
              <div class="text-center">
                <i class="fas fa-chart-bar text-4xl text-dragon-gold mb-3"></i>
                <p class="text-gray-400">Win/Loss chart visualization</p>
              </div>
            </div>
            <div class="text-center mt-4 text-sm text-gray-400">
              Graphical representation of season performance
            </div>
          </div>
          
          <!-- Recent Form -->
          <div>
            <h3 class="font-semibold text-dragon-gold mb-4">RECENT FORM (LAST 10 GAMES)</h3>
            <div class="flex flex-wrap gap-2 mb-6">
              <?php 
              // Simulated recent form (in a real app, this would come from the database)
              $recentForm = ['W', 'W', 'L', 'W', 'W', 'W', 'L', 'W', 'W', 'W'];
              foreach ($recentForm as $result): ?>
                <span class="w-8 h-8 rounded-full flex items-center justify-center font-bold 
                  <?= $result === 'W' ? 'bg-dragon-gold text-dragon-navy' : 'bg-dragon-red text-white' ?>">
                  <?= $result ?>
                </span>
              <?php endforeach; ?>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
              <div class="bg-dragon-navy/50 p-3 rounded-lg text-center border border-dragon-navy">
                <div class="text-dragon-gold font-bold text-xl">8</div>
                <div class="text-gray-300 text-sm">Wins</div>
              </div>
              <div class="bg-dragon-navy/50 p-3 rounded-lg text-center border border-dragon-navy">
                <div class="text-dragon-gold font-bold text-xl">2</div>
                <div class="text-gray-300 text-sm">Losses</div>
              </div>
              <div class="bg-dragon-navy/50 p-3 rounded-lg text-center border border-dragon-navy">
                <div class="text-dragon-gold font-bold text-xl">80%</div>
                <div class="text-gray-300 text-sm">Win Rate</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Team Leaders Section -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Points Leaders -->
        <div class="stat-card bg-dragon-dark/50 rounded-xl p-6 border border-dragon-navy hover:border-dragon-gold transition-all duration-300">
          <h3 class="font-heading text-xl text-dragon-gold mb-4 flex items-center">
            <i class="fas fa-bullseye mr-2"></i> POINTS LEADERS
          </h3>
          <div class="space-y-3">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <div class="flex items-center bg-dragon-navy/50 p-3 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-dragon-gold text-dragon-navy flex items-center justify-center font-bold mr-3"><?= $i ?></div>
                <div class="flex-grow">
                  <div class="font-medium">Player <?= $i ?></div>
                  <div class="text-xs text-gray-400"><?= rand(20, 30) ?> PPG</div>
                </div>
              </div>
            <?php endfor; ?>
          </div>
        </div>
        
        <!-- Assists Leaders -->
        <div class="stat-card bg-dragon-dark/50 rounded-xl p-6 border border-dragon-navy hover:border-dragon-gold transition-all duration-300">
          <h3 class="font-heading text-xl text-dragon-gold mb-4 flex items-center">
            <i class="fas fa-handshake mr-2"></i> ASSISTS LEADERS
          </h3>
          <div class="space-y-3">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <div class="flex items-center bg-dragon-navy/50 p-3 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-dragon-gold text-dragon-navy flex items-center justify-center font-bold mr-3"><?= $i ?></div>
                <div class="flex-grow">
                  <div class="font-medium">Player <?= $i ?></div>
                  <div class="text-xs text-gray-400"><?= rand(5, 12) ?> APG</div>
                </div>
              </div>
            <?php endfor; ?>
          </div>
        </div>
        
        <!-- Rebounds Leaders -->
        <div class="stat-card bg-dragon-dark/50 rounded-xl p-6 border border-dragon-navy hover:border-dragon-gold transition-all duration-300">
          <h3 class="font-heading text-xl text-dragon-gold mb-4 flex items-center">
            <i class="fas fa-basketball-ball mr-2"></i> REBOUNDS LEADERS
          </h3>
          <div class="space-y-3">
            <?php for ($i = 1; $i <= 3; $i++): ?>
              <div class="flex items-center bg-dragon-navy/50 p-3 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-dragon-gold text-dragon-navy flex items-center justify-center font-bold mr-3"><?= $i ?></div>
                <div class="flex-grow">
                  <div class="font-medium">Player <?= $i ?></div>
                  <div class="text-xs text-gray-400"><?= rand(8, 15) ?> RPG</div>
                </div>
              </div>
            <?php endfor; ?>
          </div>
        </div>
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
            <li><a href="schedule.php" class="text-gray-400 hover:text-white transition-colors duration-300 flex items-center">
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

    // Animate stats cards when they come into view
    const observerOptions = {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate-fade-in");
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    document.querySelectorAll('.stat-card').forEach(card => {
      observer.observe(card);
    });
  </script>
</body>
</html>