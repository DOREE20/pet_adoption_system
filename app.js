// app.js - Shared utilities for PawHome
const API_BASE = 'api.php';

// Safe API fetch
async function apiFetch(action, data = {}, method = 'POST') {
  const fd = new FormData();
  fd.append('action', action);
  Object.entries(data).forEach(([k, v]) => fd.append(k, v));
  if (method === 'GET') {
    const params = new URLSearchParams(data).toString();
    const res = await fetch(`${API_BASE}?action=${action}&${params}`);
    return res.json();
  }
  const res = await fetch(API_BASE, { method, body: fd });
  return res.json();
}

// Get current user
function getUser() {
  try {
    return JSON.parse(localStorage.getItem('pawsUser') || 'null');
  } catch {
    return null;
  }
}

// Check login & update UI
function checkLoginStatus() {
  const user = getUser();
  const welcomeMsg = document.getElementById('welcome-msg');
  const navLogin = document.getElementById('nav-login');
  const navRegister = document.getElementById('navRegister');
  const navLogout = document.getElementById('navLogout');
  const btnLogout = document.getElementById('btn-logout');
  const navDashboard = document.getElementById('nav-dashboard');
  const navbar = document.getElementById('navbar');

  if (user && welcomeMsg) {
    welcomeMsg.textContent = `Welcome, ${user.firstName}`;
    welcomeMsg.classList.remove('hidden');
  }
  if (navLogin) navLogin.classList.toggle('hidden', !!user);
  if (navRegister) navRegister.classList.toggle('hidden', !!user);
  if (navLogout) navLogout.classList.toggle('hidden', !user);
  if (btnLogout) btnLogout.classList.toggle('hidden', !user);
  if (navDashboard) navDashboard.classList.toggle('hidden', !user);
  if (navbar) navbar.classList.toggle('logged-in', !!user);
}

// Logout
function logout() {
  localStorage.removeItem('pawsUser');
  checkLoginStatus();
  showToast('Logged out');
  setTimeout(() => location.reload(), 1000);
}

// Toast notifications
function showToast(msg, duration = 3000) {
  let toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    document.body.appendChild(toast);
    // Add CSS later via style
  }
  toast.textContent = msg;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), duration);
}

// Navbar consistency helper (use in HTML)
function initNavbar() {
  checkLoginStatus();
  document.addEventListener('DOMContentLoaded', checkLoginStatus);
}

// Toggle password visibility
function togglePw(id) {
  const inp = document.getElementById(id);
  if (inp) inp.type = inp.type === 'password' ? 'text' : 'password';
}

// FAQ Toggle Functionality
function initFAQs() {
  const faqContainer = document.querySelector('.faq-container');
  if (!faqContainer) return;

  faqContainer.addEventListener('click', (e) => {
    const question = e.target.closest('.faq-question');
    if (!question) return;

    const item = question.parentElement;
    const answer = item.querySelector('.faq-answer');
    const icon = question.querySelector('.faq-icon');

    // Toggle active
    item.classList.toggle('active');
    
    // Rotate icon
    if (icon) {
      icon.style.transform = item.classList.contains('active') ? 'rotate(45deg)' : 'rotate(0deg)';
    }

    // Slide answer
    if (answer) {
      answer.style.maxHeight = item.classList.contains('active') ? answer.scrollHeight + 'px' : '0px';
    }
  });
}

// Export for global use
window.apiFetch = apiFetch;
window.getUser = getUser;
window.checkLoginStatus = checkLoginStatus;
window.logout = logout;
window.showToast = showToast;
window.togglePw = togglePw;
window.initNavbar = initNavbar;
window.initFAQs = initFAQs;

