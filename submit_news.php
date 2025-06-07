<?php
session_start();
include 'db_connect.php';

// Check if user is logged in as admin (simple version - in production use proper authentication)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php'); // You would need to create this
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $conn->prepare("INSERT INTO news (title, content) VALUES (:title, :content)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);
            $stmt->execute();
            
            $success = "News article submitted successfully!";
        } catch (PDOException $e) {
            $error = "Error submitting news: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Huruma Dragons - Submit News</title>
    <!-- Include your CSS/JS files here -->
</head>
<body>
    <section class="py-20 bg-dragon-dark">
        <div class="container mx-auto px-6 max-w-3xl">
            <h2 class="font-heading text-4xl text-dragon-gold mb-10 text-center">SUBMIT NEWS</h2>
            
            <?php if (isset($success)): ?>
            <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6">
                <?= $success ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="bg-dragon-red/20 text-red-300 p-4 rounded-lg mb-6">
                <?= $error ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="title" class="block text-dragon-gold mb-2">Title</label>
                    <input type="text" id="title" name="title" required 
                           class="w-full px-4 py-3 bg-dragon-navy/50 border border-dragon-navy rounded-lg focus:border-dragon-gold focus:outline-none text-white">
                </div>
                
                <div>
                    <label for="content" class="block text-dragon-gold mb-2">Content</label>
                    <textarea id="content" name="content" rows="8" required
                              class="w-full px-4 py-3 bg-dragon-navy/50 border border-dragon-navy rounded-lg focus:border-dragon-gold focus:outline-none text-white"></textarea>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-dragon-red hover:bg-dragon-gold hover:text-dragon-navy font-bold rounded-full transition-colors duration-300">
                        Submit News
                    </button>
                </div>
            </form>
        </div>
    </section>
</body>
</html>