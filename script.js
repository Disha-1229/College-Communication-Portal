/* =========================================================
   script.js — CampVerse Project (Final Working Version)
   Features:
   - Sidebar toggle with overlay
   - Announcements posting + category filter
   - Safe JSON parsing to prevent frontend crashes
   - Comment handling (add / delete)
========================================================= */

document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ script.js loaded");

  /* -----------------------------
     Helper: escape & parse JSON
  ----------------------------- */
  function escapeHtml(str) {
    if (!str) return "";
    return str
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  async function parseJsonSafe(response) {
    const text = await response.text();
    if (!text) return { __error: "empty_response", __raw: "" };
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error("Invalid JSON from server:", text);
      return { __error: "invalid_json", __raw: text };
    }
  }

  /* -----------------------------
     Sidebar toggle
  ----------------------------- */
  const sidebar = document.querySelector(".sidebar");
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebarClose = document.getElementById("sidebarClose");
  const overlay = document.getElementById("sidebarOverlay");
  const appContainer = document.querySelector(".app-container");

  function openSidebar() {
    sidebar.classList.add("is-open");
    overlay.style.display = "block";
    appContainer.classList.add("sidebar-is-open");
  }

  function closeSidebar() {
    sidebar.classList.remove("is-open");
    overlay.style.display = "none";
    appContainer.classList.remove("sidebar-is-open");
  }

  if (sidebarToggle) sidebarToggle.addEventListener("click", openSidebar);
  if (sidebarClose) sidebarClose.addEventListener("click", closeSidebar);
  if (overlay) overlay.addEventListener("click", closeSidebar);
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSidebar();
  });
    /* -----------------------------
     LOGIN FORM HANDLER
  ----------------------------- */
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      try {
        const response = await fetch("backend/login.php", {
          method: "POST",
          body: new FormData(loginForm),
        });
        const result = await response.json();
        alert(result.message || "Login response received");

        // ✅ Redirect after successful login
        if (result.status === "success") {
          window.location.href = "announce.php";
        }
      } catch (err) {
        console.error("Login error:", err);
        alert("Login failed. See console for details.");
      }
    });
  }

  /* -----------------------------
     SIGNUP FORM HANDLER
  ----------------------------- */
  const signupForm = document.getElementById("signupForm");
  if (signupForm) {
    signupForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      try {
        const response = await fetch("backend/signup.php", {
          method: "POST",
          body: new FormData(signupForm),
        });
        const result = await response.json();
        alert(result.message || "Signup completed");

        // ✅ Redirect after successful signup
        if (result.status === "success") {
          window.location.href = "announce.php";
        }
      } catch (err) {
        console.error("Signup error:", err);
        alert("Signup failed. Check console.");
      }
    });
  }


  /* -----------------------------
     Announcements section
  ----------------------------- */
  /* ===================================================
   CATEGORY SYSTEM — Tabs show counts, Dropdown posts
=================================================== */
const announcementForm = document.getElementById("announcementForm");
const announcementInput = document.getElementById("announcementInput");
const announcementsFeed = document.getElementById("announcementsFeed");
const categoryTabs = document.querySelectorAll(".cat-tab");
const catSelect = document.getElementById("announcementCategory");

let currentCategory = "general";

/* ----- Fetch and render announcements ----- */
async function fetchAnnouncements(category = "general") {
  try {
    const res = await fetch(`backend/post_announcement.php?category=${category}`);
    const json = await parseJsonSafe(res);

    if (json.status !== "success" || !Array.isArray(json.data)) {
      announcementsFeed.innerHTML = `<p>${json.message || "Error fetching posts"}</p>`;
      return;
    }

    const data = json.data;

    // --- render posts ---
    if (!data.length) {
      announcementsFeed.innerHTML = `<p>No posts yet in this category.</p>`;
    } else {
      announcementsFeed.innerHTML = data
        .map(
          (post) => `
          <div class="announcement">
        
            <div class="post-header">
              <span class="author">${escapeHtml(post.username || "Anonymous")}</span>
              &nbsp;&nbsp;
              <span class="time">${new Date(post.created_at).toLocaleDateString()}&nbsp;&nbsp;${new Date(post.created_at).toLocaleTimeString()}</span>
              &nbsp;&nbsp;
              <span class="category-tag">(${escapeHtml(post.category.charAt(0).toUpperCase() + post.category.slice(1))})</span>
            </div>


            <p class="post-text">${escapeHtml(post.content)}</p>
            <button class="delete-btn" data-id="${post.id}">
              <i class="fas fa-trash"></i>

            </button>
          </div>
          `)
        .join("");
    }

    // --- update tab counts dynamically ---
    updateTabCounts();
  } catch (err) {
    console.error("Fetch announcements error:", err);
    announcementsFeed.innerHTML = `<p>Failed to load posts.</p>`;
  }
}

/* ----- Tab counts (call separately for each tab) ----- */
async function updateTabCounts() {
  try {
    const res = await fetch("backend/post_announcement.php?category=general"); // get all posts
    const json = await parseJsonSafe(res);
    if (json.status !== "success" || !Array.isArray(json.data)) return;

    const counts = {};
    json.data.forEach((p) => {
      const cat = (p.category || "general").toLowerCase();
      counts[cat] = (counts[cat] || 0) + 1;
    });

    categoryTabs.forEach((tab) => {
      const cat = (tab.dataset.cat || "general").toLowerCase();
      const c = counts[cat] || 0;
      tab.innerHTML = `${tab.textContent.split(" (")[0]} (${c})`;
    });
  } catch (e) {
    console.error("Count update error:", e);
  }
}

/* ----- TAB CLICK — view posts for that category ----- */
if (categoryTabs) {
  categoryTabs.forEach((tab) => {
    tab.addEventListener("click", (e) => {
      e.preventDefault();
      categoryTabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      currentCategory = (tab.dataset.cat || "general").toLowerCase().trim();
      console.log("[TAB] viewing category:", currentCategory);
      fetchAnnouncements(currentCategory);
    });
  });
}

/* ----- POST FORM ----- */
if (announcementForm) {
  announcementForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const content = announcementInput.value.trim();
    if (!content) return alert("Cannot post empty content!");

    const selectedCat = (catSelect?.value || "general").toLowerCase().trim();
    console.log("[POST] posting to:", selectedCat);

    try {
      const fd = new FormData();
      fd.append("content", content);
      fd.append("category", selectedCat);

      const res = await fetch("backend/post_announcement.php", {
        method: "POST",
        body: fd,
      });
      const json = await parseJsonSafe(res);

      if (json.status === "success") {
        announcementInput.value = "";
        fetchAnnouncements(currentCategory); // refresh current view
      } else {
        alert(json.message || "Failed to post");
        console.error("Post error:", json);
      }
    } catch (err) {
      console.error("Post error:", err);
      alert("Network error while posting");
    }
  });
}

/* ----- INITIAL LOAD ----- */
fetchAnnouncements("general");

// Delegated delete handler (replace existing delete code)
if (announcementsFeed) {
  announcementsFeed.addEventListener("click", async (e) => {
    const btn = e.target.closest(".delete-btn");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    if (!id) {
      console.error("[delete] missing data-id on button", btn);
      return alert("Delete failed: missing id");
    }

    if (!confirm("Delete this post?")) return;

    try {
      const res = await fetch("backend/post_announcement.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `delete_id=${encodeURIComponent(id)}`
      });

      const json = await parseJsonSafe(res); // you have this helper
      if (json.__error) {
        console.error("[delete] server returned invalid JSON:", json);
        return alert("Server error — check console/network.");
      }

      if (json.status === "success") {
        // immediate UI feedback
        const postEl = btn.closest(".announcement");
        if (postEl) postEl.remove();
        // refresh counts/view (optional)
        if (typeof fetchAnnouncements === "function") fetchAnnouncements(currentCategory || "general");
      } else {
        alert(json.message || "Delete failed");
      }
    } catch (err) {
      console.error("[delete] network error:", err);
      alert("Network error while deleting post");
    }
  });
}


});

const logoutBtn = document.getElementById("logoutBtn") || document.querySelector(".logout-btn");

if (logoutBtn) {
  logoutBtn.addEventListener("click", async (e) => {
    e.preventDefault();
    try {
      await fetch("backend/logout.php");
      window.location.href = "index.html";
    } catch (err) {
      console.error("Logout error:", err);
      window.location.href = "index.html";
    }
  });
}

