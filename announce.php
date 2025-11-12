<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Announcements | CampVerse</title>

  <!-- Styles -->
  <link rel="stylesheet" href="css/announce.css">
  <link rel="stylesheet" href="css/icons/all.min.css">
  <style>
    /* overlay for background click */
    .sidebar-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      z-index: 500;
      display: none;
    }
  </style>
</head>

<body>
  <div class="app-container">
    <!-- Overlay for closing sidebar -->
    <div id="sidebarOverlay" class="sidebar-overlay" aria-hidden="true"></div>

    <!-- ===== Sidebar ===== -->
    <aside class="sidebar" role="navigation" aria-hidden="true" aria-label="Main sidebar">
      <div class="sidebar-header">
        <button id="sidebarClose" class="sidebar-close-btn" aria-label="Close sidebar">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="edit-profile.html" class="nav-item"><i class="fas fa-user-edit"></i><span>Edit Profile</span></a>
        <a href="about.html" class="nav-item"><i class="fas fa-info-circle"></i><span>About CampVerse</span></a>
        <a href="contact.html" class="nav-item"><i class="fas fa-envelope"></i><span>Contact Us</span></a>
      </nav>


      <div class="sidebar-footer">
        <a href="backend/logout.php" class="nav-item logout-btn">
          <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
      </div>
    </aside>

    <!-- ===== Main Content ===== -->
    <main class="main-content" id="mainContent">
      <header class="main-header">
        <button id="sidebarToggle" class="sidebar-toggle-btn" aria-controls="sidebar" aria-expanded="false" aria-label="Open sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="main-logo">CampVerse</h1>
        <div class="header-spacer"></div>
      </header>

      <div class="feed-container">
        <h2 class="feed-title">Recent Thoughts</h2>

        <!-- Post new announcement -->
        <!-- Category tabs (top) -->
        <div class="category-tabs" role="tablist" aria-label="Post categories">
          <button class="cat-tab active" data-cat="general" role="tab" aria-selected="true">General</button>
          <button class="cat-tab" data-cat="events" role="tab">Events</button>
          <button class="cat-tab" data-cat="academics" role="tab">Academics</button>
          <button class="cat-tab" data-cat="chillhub" role="tab">ChillHub</button>
          <button class="cat-tab" data-cat="confession" role="tab">Confession</button>
          <button class="cat-tab" data-cat="emergency" role="tab">Emergency</button>
        </div>

<!-- Post new announcement (with category selector) -->
        <div class="post-announcement-wrapper">
          <form id="announcementForm" class="post-form">
          <select id="announcementCategory" name="category" aria-label="Select category">
            <option value="general">General</option>
            <option value="events">Events</option>
            <option value="academics">Academics</option>
            <option value="chillhub">ChillHub</option>
            <option value="confession">Confession</option>
            <option value="emergency">Emergency</option>
          </select>

          <textarea id="announcementInput" name="content" placeholder="Post a new thought..." rows="1"></textarea>
          <button type="submit">Post</button>
          </form>
        </div>


        <!-- Announcements feed -->
        <section id="announcementsFeed" class="announcements-feed"></section>
      </div>
    </main>
  </div>

  <!-- JS -->
  <script src="javascript/script.js"></script>

  <!-- Overlay and ARIA sync helper -->
  <script>
    (function () {
      const overlay = document.getElementById('sidebarOverlay');
      const sidebar = document.querySelector('.sidebar');
      const appContainer = document.querySelector('.app-container');
      const toggle = document.getElementById('sidebarToggle');
      const closeBtn = document.getElementById('sidebarClose');

      function openSidebar() {
        sidebar.classList.add('is-open');
        sidebar.setAttribute('aria-hidden', 'false');
        if (appContainer) appContainer.classList.add('sidebar-is-open');
        overlay.style.display = 'block';
        toggle.setAttribute('aria-expanded', 'true');
      }

      function closeSidebar() {
        sidebar.classList.remove('is-open');
        sidebar.setAttribute('aria-hidden', 'true');
        if (appContainer) appContainer.classList.remove('sidebar-is-open');
        overlay.style.display = 'none';
        toggle.setAttribute('aria-expanded', 'false');
      }

      // Toggle open
      toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = sidebar.classList.contains('is-open');
        if (isOpen) closeSidebar();
        else openSidebar();
      });

      // Close button
      closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        closeSidebar();
      });

      // Overlay click
      overlay.addEventListener('click', (e) => {
        e.preventDefault();
        closeSidebar();
      });

      // ESC key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
          closeSidebar();
        }
      });
    })();
  </script>
</body>
</html>
