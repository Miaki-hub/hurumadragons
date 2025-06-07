<?php
include 'db_connect.php';

// Fetch all news articles, newest first with pagination
$articlesPerPage = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $articlesPerPage;

// Get total count for pagination
$totalStmt = $conn->query("SELECT COUNT(*) FROM news");
$totalArticles = $totalStmt->fetchColumn();
$totalPages = ceil($totalArticles / $articlesPerPage);

// Fetch articles for current page
$stmt = $conn->prepare("
    SELECT * FROM news 
    ORDER BY date DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $articlesPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch 5 most recent news for the featured section
$featuredStmt = $conn->query("
    SELECT id, title, content, date
    FROM news
    ORDER BY date DESC
    LIMIT 5
");
$featuredStories = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team News | Huruma Dragons</title>
  <meta name="description" content="Latest news and updates from Huruma Dragons, Kenya's premier basketball team">
  
  <!-- Favicon -->
  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  
  <!-- Glide.js CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.core.min.css">
  
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            dragon: {
              red: '#d62828',
              navy: '#003049',
              gold: '#fcbf49',
              dark: '#1a1a1a',
              light: '#eae2b7'
            }
          },
          fontFamily: {
            heading: ['Bebas Neue', 'sans-serif'],
            body: ['Montserrat', 'sans-serif'],
            accent: ['Rajdhani', 'sans-serif']
          },
          animation: {
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            'fade-in': 'fadeIn 0.5s ease-in',
            'float': 'float 6s ease-in-out infinite',
            'bounce-slow': 'bounceSlow 2s infinite'
          },
          keyframes: {
            fadeIn: {
              '0%': { opacity: '0' },
              '100%': { opacity: '1' }
            },
            float: {
              '0%, 100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-10px)' }
            },
            bounceSlow: {
              '0%, 100%': { transform: 'translateY(0)' },
              '50%': { transform: 'translateY(-15px)' }
            }
          }
        }
      }
    }
  </script>
  <style type="text/tailwindcss">
    @layer utilities {
      .text-stroke {
        -webkit-text-stroke: 2px theme('colors.dragon.navy');
        text-stroke: 2px theme('colors.dragon.navy');
      }
      .gradient-text {
        background-clip: text;
        -webkit-background-clip: text;
        color: transparent;
        background-image: linear-gradient(45deg, theme('colors.dragon.gold'), theme('colors.dragon.red'));
      }
      .news-card {
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        perspective: 1000px;
        transform-style: preserve-3d;
      }
      .news-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
      }
      .news-card-inner {
        transition: transform 0.6s;
        transform-style: preserve-3d;
      }
      .news-card:hover .news-card-inner {
        transform: rotateY(5deg);
      }
      .category-filter {
        scrollbar-width: thin;
        scrollbar-color: theme('colors.dragon.gold') transparent;
      }
      .category-filter::-webkit-scrollbar {
        height: 8px;
      }
      .category-filter::-webkit-scrollbar-thumb {
        background-color: theme('colors.dragon.gold');
        border-radius: 4px;
      }
      .filter-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }
      .filter-btn.active {
        background: linear-gradient(135deg, theme('colors.dragon.red'), theme('colors.dragon.gold'));
        color: white;
        transform: scale(1.05);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .news-image {
        mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%);
      }
      .glide__slide {
        opacity: 0.6;
        transition: all 0.3s ease;
      }
      .glide__slide.glide__slide--active {
        opacity: 1;
        transform: scale(1.05);
      }
      .placeholder-image {
        background: linear-gradient(135deg, theme('colors.dragon.navy') 0%, theme('colors.dragon.dark') 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: theme('colors.dragon.gold');
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body class="font-body bg-dragon-dark text-white">

  <!-- Animated Background Elements -->
  <div class="fixed inset-0 overflow-hidden -z-10">
    <div class="absolute inset-0 bg-gradient-to-br from-dragon-navy/90 to-dragon-dark/90"></div>
    <div class="absolute inset-0 opacity-10" style="background-image: url('assets/images/dragon-pattern.png'); background-size: 300px;"></div>
    <div class="absolute top-1/4 left-1/4 w-32 h-32 rounded-full bg-dragon-gold/10 animate-float"></div>
    <div class="absolute top-1/3 right-1/4 w-24 h-24 rounded-full bg-dragon-red/10 animate-float" style="animation-delay: 1s;"></div>
    <div class="absolute bottom-1/4 right-1/3 w-20 h-20 rounded-full bg-dragon-gold/10 animate-float" style="animation-delay: 2s;"></div>
  </div>

  <!-- Navigation -->
  <header class="fixed w-full z-40 bg-dragon-navy/90 backdrop-blur-md shadow-xl">
    <div class="container mx-auto px-6 py-3">
      <div class="flex justify-between items-center">
        <a href="index.php" class="flex items-center space-x-3 group">
          <img src="assets/images/logo.png" alt="Huruma Dragons" class="h-14 w-auto transition-all duration-500 group-hover:rotate-6 group-hover:scale-110">
          <span class="font-heading text-4xl text-dragon-gold tracking-wider group-hover:text-dragon-red transition-colors duration-300">HURUMA DRAGONS</span>
        </a>
        
        <nav class="hidden lg:block">
          <ul class="flex space-x-8">
            <li><a href="players.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              Team
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-500"></span>
            </a></li>
            <li><a href="schedule.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              Schedule
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-500"></span>
            </a></li>
            <li><a href="team_stats.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              Stats
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-500"></span>
            </a></li>
            <li><a href="news.php" class="relative group font-semibold uppercase text-sm tracking-wider transition-colors duration-300 hover:text-dragon-gold">
              News
              <span class="absolute left-0 bottom-0 h-0.5 bg-dragon-gold w-0 group-hover:w-full transition-all duration-500"></span>
            </a></li>
            <li><a href="#tickets" class="px-5 py-2.5 bg-gradient-to-r from-dragon-red to-dragon-gold hover:from-dragon-gold hover:to-dragon-red text-dragon-navy font-bold rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
              Get Tickets <i class="fas fa-ticket-alt ml-2"></i>
            </a></li>
            <?php if(isset($_SESSION['admin_logged_in'])): ?>
            <li><a href="submit_news.php" class="ml-4 px-4 py-2 bg-dragon-gold text-dragon-navy hover:bg-dragon-red font-bold rounded-full transition-colors duration-300">
              <i class="fas fa-lock mr-2"></i>Admin
            </a></li>
            <?php endif; ?>
          </ul>
        </nav>
        
        <button id="mobile-menu-button" class="lg:hidden text-white focus:outline-none transition-all duration-300 hover:scale-110 hover:text-dragon-gold">
          <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="lg:hidden hidden bg-dragon-navy/95 px-6 py-4 shadow-xl backdrop-blur-md">
      <ul class="space-y-4">
        <li><a href="players.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300 flex items-center">
          <i class="fas fa-users mr-3 text-dragon-gold"></i> Team Roster
        </a></li>
        <li><a href="schedule.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300 flex items-center">
          <i class="fas fa-calendar-alt mr-3 text-dragon-gold"></i> Schedule
        </a></li>
        <li><a href="team_stats.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300 flex items-center">
          <i class="fas fa-chart-bar mr-3 text-dragon-gold"></i> Team Stats
        </a></li>
        <li><a href="news.php" class="block font-semibold uppercase text-sm tracking-wider py-2 hover:text-dragon-gold transition-colors duration-300 flex items-center">
          <i class="fas fa-newspaper mr-3 text-dragon-gold"></i> Latest News
        </a></li>
        <li><a href="#tickets" class="block px-4 py-3 bg-gradient-to-r from-dragon-red to-dragon-gold hover:from-dragon-gold hover:to-dragon-red text-dragon-navy font-bold rounded-full text-center transition-all duration-300 mt-2 transform hover:scale-105">
          <i class="fas fa-ticket-alt mr-2"></i> Get Tickets
        </a></li>
        <?php if(isset($_SESSION['admin_logged_in'])): ?>
        <li><a href="submit_news.php" class="block px-4 py-2 bg-dragon-gold text-dragon-navy hover:bg-dragon-red font-bold rounded-full text-center transition-colors duration-300 mt-2">
          <i class="fas fa-lock mr-2"></i> Admin Panel
        </a></li>
        <?php endif; ?>
      </ul>
    </div>
  </header>

  <!-- Page Header -->
  <section class="pt-40 pb-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-dragon-navy/90 to-dragon-dark/80 z-0"></div>
    <div class="absolute inset-0 z-0 opacity-20" style="background-image: url('assets/images/court-texture.jpg'); background-size: cover;"></div>
    
    <div class="container mx-auto px-6 relative z-10 text-center">
      <h1 class="font-heading text-5xl md:text-6xl lg:text-7xl text-dragon-gold mb-6 animate__animated animate__fadeInDown">
        <span class="gradient-text">TEAM NEWS</span>
      </h1>
      <div class="w-24 h-1.5 bg-gradient-to-r from-dragon-gold to-dragon-red mx-auto mb-8 rounded-full"></div>
      <p class="text-xl md:text-2xl max-w-3xl mx-auto text-dragon-light font-medium animate__animated animate__fadeIn animate__delay-1s">
        Stay updated with the latest news, updates, and announcements from Huruma Dragons
      </p>
      
      <!-- News Stats Summary -->
      <div class="max-w-4xl mx-auto mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="bg-dragon-navy/70 backdrop-blur-sm p-4 rounded-xl border border-dragon-gold/20 hover:border-dragon-gold/50 transition-all duration-300">
          <div class="text-3xl font-bold text-dragon-gold mb-1"><?= $totalArticles ?></div>
          <div class="text-sm text-dragon-light uppercase tracking-wider">Total News</div>
        </div>
        <div class="bg-dragon-navy/70 backdrop-blur-sm p-4 rounded-xl border border-dragon-gold/20 hover:border-dragon-gold/50 transition-all duration-300">
          <div class="text-3xl font-bold text-dragon-gold mb-1"><?= date('Y') ?></div>
          <div class="text-sm text-dragon-light uppercase tracking-wider">Season</div>
        </div>
        <div class="bg-dragon-navy/70 backdrop-blur-sm p-4 rounded-xl border border-dragon-gold/20 hover:border-dragon-gold/50 transition-all duration-300">
          <div class="text-3xl font-bold text-dragon-gold mb-1">24/7</div>
          <div class="text-sm text-dragon-light uppercase tracking-wider">Updates</div>
        </div>
        <div class="bg-dragon-navy/70 backdrop-blur-sm p-4 rounded-xl border border-dragon-gold/20 hover:border-dragon-gold/50 transition-all duration-300">
          <div class="text-3xl font-bold text-dragon-gold mb-1">100%</div>
          <div class="text-sm text-dragon-light uppercase tracking-wider">Dragon Spirit</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured News Carousel -->
  <div class="py-12 bg-gradient-to-b from-dragon-navy/30 to-transparent">
    <div class="container mx-auto px-6">
      <h2 class="font-heading text-3xl text-dragon-gold mb-8 text-center">
        <span class="border-b-2 border-dragon-gold pb-1">Featured Stories</span>
      </h2>
      
      <div class="glide max-w-6xl mx-auto">
        <div class="glide__track" data-glide-el="track">
          <ul class="glide__slides">
            <?php foreach ($featuredStories as $index => $story): 
              $imageIndex = ($index % 3) + 1; // Cycles through 1, 2, 3
            ?>
            <li class="glide__slide">
              <div class="bg-dragon-navy/70 backdrop-blur-sm rounded-xl overflow-hidden border border-dragon-gold/20 hover:border-dragon-gold/50 transition-all duration-300 h-full">
                <div class="relative h-64 overflow-hidden">
                  <img src="assets/images/news-<?= $imageIndex ?>.jpg" 
                       alt="<?= htmlspecialchars($story['title']) ?>" 
                       class="w-full h-full object-cover news-image transition-transform duration-500 hover:scale-105">
                  <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-dragon-navy to-transparent p-4 pt-10">
                    <h3 class="font-heading text-2xl text-dragon-gold"><?= htmlspecialchars($story['title']) ?></h3>
                    <div class="flex items-center text-dragon-light text-sm">
                      <span class="bg-dragon-gold/20 text-dragon-gold px-2 py-1 rounded mr-3">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <?= date('M j, Y', strtotime($story['date'])) ?>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="p-5">
                  <div class="text-dragon-light text-sm mb-4 line-clamp-3">
                    <?= nl2br(htmlspecialchars(substr($story['content'], 0, 200) . '...')) ?>
                  </div>
                  <a href="news_single.php?id=<?= $story['id'] ?>" class="block w-full text-center px-4 py-2.5 bg-gradient-to-r from-dragon-red to-dragon-gold hover:from-dragon-gold hover:to-dragon-red text-dragon-navy font-bold rounded-lg transition-all duration-300 transform hover:scale-105">
                    Read Story <i class="fas fa-arrow-right ml-2"></i>
                  </a>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="glide__arrows" data-glide-el="controls">
          <button class="glide__arrow glide__arrow--left absolute left-0 top-1/2 transform -translate-y-1/2 bg-dragon-navy/80 hover:bg-dragon-gold text-dragon-gold hover:text-dragon-navy w-10 h-10 rounded-full shadow-lg transition-all duration-300" data-glide-dir="<">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button class="glide__arrow glide__arrow--right absolute right-0 top-1/2 transform -translate-y-1/2 bg-dragon-navy/80 hover:bg-dragon-gold text-dragon-gold hover:text-dragon-navy w-10 h-10 rounded-full shadow-lg transition-all duration-300" data-glide-dir=">">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- News Grid -->
  <main class="py-12 relative">
    <!-- Animated floating elements -->
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
      <div class="absolute top-1/4 left-1/6 w-6 h-6 rounded-full bg-dragon-gold/20 animate-float"></div>
      <div class="absolute top-2/3 right-1/5 w-8 h-8 rounded-full bg-dragon-red/20 animate-float" style="animation-delay: 1.5s;"></div>
      <div class="absolute bottom-1/4 left-1/3 w-5 h-5 rounded-full bg-dragon-gold/20 animate-float" style="animation-delay: 2.5s;"></div>
    </div>
    
    <div class="container mx-auto px-6 relative">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($news as $index => $item): 
          $imageIndex = ($index % 3) + 1; // Cycles through 1, 2, 3
        ?>
        <article class="news-card">
          <div class="news-card-inner h-full bg-dragon-navy/80 rounded-xl overflow-hidden shadow-lg border border-dragon-gold/10 hover:border-dragon-gold/30">
            <!-- News Image -->
            <div class="relative h-64 overflow-hidden">
              <img src="assets/images/news-<?= $imageIndex ?>.jpg" 
                   alt="<?= htmlspecialchars($item['title']) ?>" 
                   class="w-full h-full object-cover transition-transform duration-700 hover:scale-110">
              <!-- Date Badge -->
              <div class="absolute bottom-4 left-4 bg-dragon-gold/90 text-dragon-navy px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-md">
                <i class="far fa-calendar-alt mr-1"></i>
                <?= date('M j, Y', strtotime($item['date'])) ?>
              </div>
            </div>
            
            <!-- News Content -->
            <div class="p-6">
              <div class="mb-5">
                <h2 class="font-heading text-2xl text-dragon-gold mb-2"><?= htmlspecialchars($item['title']) ?></h2>
              </div>
              
              <!-- Excerpt -->
              <div class="text-dragon-light text-sm mb-6 line-clamp-3">
                <?= nl2br(htmlspecialchars(substr($item['content'], 0, 200) . '...')) ?>
              </div>
              
              <!-- Read More Button -->
              <div class="flex justify-between items-center">
                <a href="news_single.php?id=<?= $item['id'] ?>" class="flex-1 text-center px-4 py-2.5 bg-gradient-to-r from-dragon-red to-dragon-gold hover:from-dragon-gold hover:to-dragon-red text-dragon-navy font-bold rounded-lg transition-all duration-300 transform hover:scale-105 mr-3">
                  Read More
                </a>
                <button class="w-10 h-10 flex items-center justify-center bg-dragon-navy hover:bg-dragon-gold text-dragon-gold hover:text-dragon-navy rounded-lg border border-dragon-gold/20 hover:border-dragon-gold transition-all duration-300">
                  <i class="fas fa-share-alt"></i>
                </button>
              </div>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
      
      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="flex justify-center mt-16">
        <nav class="flex items-center space-x-2">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="pagination-link px-4 py-2 rounded-full hover:bg-dragon-navy transition-colors duration-300">
              <i class="fas fa-chevron-left"></i>
            </a>
          <?php endif; ?>
          
          <?php 
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);
          
          if ($start > 1): ?>
            <a href="?page=1" class="pagination-link px-4 py-2 rounded-full hover:bg-dragon-navy transition-colors duration-300">1</a>
            <?php if ($start > 2): ?>
              <span class="px-2 text-gray-400">...</span>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?= $i ?>" class="pagination-link px-4 py-2 rounded-full <?= $i == $page ? 'active bg-dragon-red' : 'hover:bg-dragon-navy' ?> transition-colors duration-300">
              <?= $i ?>
            </a>
          <?php endfor; ?>
          
          <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
              <span class="px-2 text-gray-400">...</span>
            <?php endif; ?>
            <a href="?page=<?= $totalPages ?>" class="pagination-link px-4 py-2 rounded-full hover:bg-dragon-navy transition-colors duration-300">
              <?= $totalPages ?>
            </a>
          <?php endif; ?>
          
          <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="pagination-link px-4 py-2 rounded-full hover:bg-dragon-navy transition-colors duration-300">
              <i class="fas fa-chevron-right"></i>
            </a>
          <?php endif; ?>
        </nav>
      </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Newsletter CTA -->
  <section class="py-16 bg-gradient-to-r from-dragon-navy to-dragon-dark relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: url('assets/images/court-lines.png'); background-size: cover;"></div>
    <div class="container mx-auto px-6 relative z-10">
      <div class="max-w-4xl mx-auto bg-dragon-navy/70 backdrop-blur-sm rounded-xl p-8 md:p-12 border border-dragon-gold/20 shadow-2xl">
        <div class="flex flex-col md:flex-row items-center">
          <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
            <h2 class="font-heading text-3xl md:text-4xl text-dragon-gold mb-4">
              Stay Updated
            </h2>
            <p class="text-dragon-light mb-6">
              Subscribe to our newsletter to get the latest news, updates and exclusive offers directly to your inbox.
            </p>
            <form class="flex flex-col sm:flex-row gap-4">
              <input type="email" placeholder="Your email address" class="flex-grow px-6 py-3 rounded-lg bg-dragon-navy/50 border border-dragon-gold/20 focus:border-dragon-gold focus:outline-none text-white placeholder-gray-400">
              <button type="submit" class="px-6 py-3 bg-gradient-to-r from-dragon-gold to-dragon-red hover:from-dragon-red hover:to-dragon-gold text-dragon-navy font-bold rounded-lg transition-all duration-300 transform hover:scale-105">
                Subscribe
              </button>
            </form>
          </div>
          <div class="md:w-1/2">
            <img src="assets/images/newsletter.png" alt="Newsletter" class="w-full h-auto rounded-lg shadow-xl transform rotate-1 hover:rotate-0 transition-transform duration-500">
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

    // Add smooth scrolling to top when paginating
    document.querySelectorAll('.pagination-link').forEach(link => {
      link.addEventListener('click', function(e) {
        if (!this.classList.contains('active')) {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        }
      });
    });

    // Animate news cards when they come into view
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

    document.querySelectorAll('.news-card').forEach(card => {
      observer.observe(card);
    });
  </script>
</body>
</html>
