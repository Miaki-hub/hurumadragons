<?php 
include 'db_connect.php';

// Fetch all scheduled games with opponent information
$stmt = $conn->prepare("
    SELECT g.*, t.name as opponent_name, t.logo as opponent_logo, 
           h.name as home_team_name, h.logo as home_team_logo
    FROM games g
    LEFT JOIN teams t ON g.opponent_id = t.id
    LEFT JOIN teams h ON g.home_team_id = h.id
    ORDER BY g.game_date, g.game_time
");
$stmt->execute();
$games = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get distinct game types for filtering
$game_types = array_unique(array_column($games, 'game_type'));
sort($game_types);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Game Schedule | Huruma Dragons</title>
  <meta name="description" content="View the upcoming games and schedule for the Huruma Dragons basketball team">
  
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
      .game-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(252, 191, 73, 0.1);
      }
      .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        border-color: rgba(252, 191, 73, 0.3);
      }
      .type-filter {
        scrollbar-width: thin;
        scrollbar-color: theme('colors.dragon.gold') transparent;
      }
      .type-filter::-webkit-scrollbar {
        height: 6px;
      }
      .type-filter::-webkit-scrollbar-thumb {
        background-color: theme('colors.dragon.gold');
        border-radius: 3px;
      }
      .filter-btn {
        transition: all 0.2s ease;
      }
      .filter-btn.active {
        background-color: theme('colors.dragon.red');
        color: white;
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

  <!-- Page Header -->
  <section class="pt-32 pb-20 bg-gradient-to-b from-dragon-navy to-dragon-dark">
    <div class="container mx-auto px-6 text-center">
      <h1 class="font-heading text-4xl md:text-5xl text-dragon-gold mb-4 animate-fade-in">GAME SCHEDULE</h1>
      <div class="w-20 h-1 bg-dragon-red mx-auto mb-6"></div>
      <p class="text-xl max-w-2xl mx-auto text-gray-300">Upcoming games and match results for the Huruma Dragons</p>
    </div>
  </section>

  <!-- Game Type Filter -->
  <div class="sticky top-16 z-20 bg-dragon-dark border-b border-dragon-navy/50">
    <div class="container mx-auto px-6">
      <div class="type-filter flex overflow-x-auto py-4 gap-2">
        <button class="filter-btn active px-4 py-2 bg-dragon-navy text-dragon-gold rounded-full text-sm font-bold whitespace-nowrap" data-type="all">
          All Games
        </button>
        <?php foreach ($game_types as $type): ?>
        <button class="filter-btn px-4 py-2 bg-dragon-navy text-dragon-gold rounded-full text-sm font-bold whitespace-nowrap" data-type="<?= htmlspecialchars(strtolower($type)) ?>">
          <?= htmlspecialchars($type) ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Schedule Table -->
  <main class="py-12">
    <div class="container mx-auto px-6">
      <div class="grid grid-cols-1 gap-6">
        <?php foreach ($games as $game): 
          $game_date = new DateTime($game['game_date']);
          $game_time = new DateTime($game['game_time']);
          $is_home_game = $game['home_team_id'] == 1; // Assuming 1 is Huruma Dragons team ID
          $is_past_game = new DateTime() > new DateTime($game['game_date'] . ' ' . $game['game_time']);
        ?>
        <article class="game-card bg-dragon-navy/80 rounded-lg overflow-hidden animate-fade-in" data-type="<?= htmlspecialchars(strtolower($game['game_type'])) ?>">
          <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <!-- Game Date/Time -->
              <div class="flex-shrink-0 text-center md:text-left">
                <div class="font-heading text-2xl text-dragon-gold">
                  <?= $game_date->format('M j, Y') ?>
                </div>
                <div class="text-gray-300">
                  <?= $game_time->format('g:i A') ?>
                </div>
                <div class="mt-2">
                  <?php if ($is_past_game): ?>
                    <?php if ($game['home_score'] !== null && $game['away_score'] !== null): ?>
                      <?php $is_win = ($is_home_game && $game['home_score'] > $game['away_score']) || (!$is_home_game && $game['away_score'] > $game['home_score']); ?>
                      <span class="result-badge <?= $is_win ? 'win' : 'loss' ?>">
                        <?= $is_win ? 'WIN' : 'LOSS' ?> 
                        <?= $is_home_game ? $game['home_score'] . '-' . $game['away_score'] : $game['away_score'] . '-' . $game['home_score'] ?>
                      </span>
                    <?php else: ?>
                      <span class="result-badge upcoming">COMPLETED</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="result-badge upcoming">UPCOMING</span>
                  <?php endif; ?>
                </div>
              </div>
              
              <!-- Teams -->
              <div class="flex-grow flex flex-col sm:flex-row items-center justify-center gap-6">
                <!-- Home Team -->
                <div class="flex flex-col items-center <?= $is_home_game ? 'order-1' : 'order-3' ?>">
                  <?php if ($is_home_game): ?>
                    <img src="assets/images/logo.png" alt="Huruma Dragons" class="h-16 w-auto">
                    <div class="font-bold mt-2">Huruma Dragons</div>
                  <?php else: ?>
                    <?php if ($game['home_team_logo']): ?>
                      <img src="<?= htmlspecialchars($game['home_team_logo']) ?>" alt="<?= htmlspecialchars($game['home_team_name']) ?>" class="h-16 w-auto">
                    <?php else: ?>
                      <div class="h-16 w-16 bg-dragon-dark rounded-full flex items-center justify-center">
                        <i class="fas fa-basketball-ball text-2xl text-dragon-gold"></i>
                      </div>
                    <?php endif; ?>
                    <div class="font-bold mt-2"><?= htmlspecialchars($game['home_team_name']) ?></div>
                  <?php endif; ?>
                </div>
                
                <!-- VS -->
                <div class="order-2 flex flex-col items-center">
                  <div class="font-heading text-2xl text-dragon-gold">VS</div>
                  <div class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($game['game_type']) ?></div>
                </div>
                
                <!-- Away Team -->
                <div class="flex flex-col items-center <?= $is_home_game ? 'order-3' : 'order-1' ?>">
                  <?php if (!$is_home_game): ?>
                    <img src="assets/images/logo.png" alt="Huruma Dragons" class="h-16 w-auto">
                    <div class="font-bold mt-2">Huruma Dragons</div>
                  <?php else: ?>
                    <?php if ($game['opponent_logo']): ?>
                      <img src="<?= htmlspecialchars($game['opponent_logo']) ?>" alt="<?= htmlspecialchars($game['opponent_name']) ?>" class="h-16 w-auto">
                    <?php else: ?>
                      <div class="h-16 w-16 bg-dragon-dark rounded-full flex items-center justify-center">
                        <i class="fas fa-basketball-ball text-2xl text-dragon-gold"></i>
                      </div>
                    <?php endif; ?>
                    <div class="font-bold mt-2"><?= htmlspecialchars($game['opponent_name']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              
              <!-- Game Info -->
              <div class="flex-shrink-0 text-center md:text-right">
                <div class="text-gray-300 mb-2">
                  <i class="fas fa-map-marker-alt text-dragon-gold mr-2"></i>
                  <?= htmlspecialchars($game['location']) ?>
                </div>
                <div class="flex justify-center md:justify-end gap-2">
                  <?php if ($is_past_game): ?>
                    <a href="game_recap.php?id=<?= $game['id'] ?>" class="px-3 py-1 bg-dragon-navy hover:bg-dragon-gold hover:text-dragon-navy text-white rounded-full text-sm font-bold transition-colors duration-300">
                      Recap
                    </a>
                  <?php else: ?>
                    <a href="#tickets" class="px-3 py-1 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy text-white rounded-full text-sm font-bold transition-colors duration-300">
                      Tickets
                    </a>
                  <?php endif; ?>
                  <a href="#directions" class="px-3 py-1 bg-dragon-navy hover:bg-dragon-gold hover:text-dragon-navy text-white rounded-full text-sm font-bold transition-colors duration-300">
                    Directions
                  </a>
                </div>
              </div>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      
      <!-- Empty State -->
      <div id="empty-state" class="hidden py-16 text-center">
        <div class="bg-dragon-navy/50 rounded-xl p-10 max-w-md mx-auto border border-dragon-navy">
          <div class="text-dragon-gold mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <h3 class="font-heading text-2xl text-dragon-gold mb-4">No Games Scheduled</h3>
          <p class="text-gray-400 mb-6">There are no games matching the selected filter.</p>
          <button class="px-6 py-3 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full transition-colors duration-300" onclick="resetFilters()">
            Show All Games
          </button>
        </div>
      </div>
    </div>
  </main>

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

    // Filter games by type
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const type = this.dataset.type;
        const games = document.querySelectorAll('.game-card');
        let visibleCount = 0;
        
        // Filter games
        games.forEach(game => {
          if (type === 'all' || game.dataset.type === type) {
            game.style.display = 'block';
            visibleCount++;
          } else {
            game.style.display = 'none';
          }
        });
        
        // Show empty state if no games match
        const emptyState = document.getElementById('empty-state');
        if (visibleCount === 0) {
          emptyState.classList.remove('hidden');
        } else {
          emptyState.classList.add('hidden');
        }
        
        // Smooth scroll to top of grid
        window.scrollTo({
          top: document.querySelector('main').offsetTop - 100,
          behavior: 'smooth'
        });
      });
    });
    
    // Reset all filters
    function resetFilters() {
      document.querySelector('.filter-btn[data-type="all"]').click();
    }

    // Animate game cards when they come into view
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

    document.querySelectorAll('.game-card').forEach(card => {
      observer.observe(card);
    });
  </script>
</body>
</html>