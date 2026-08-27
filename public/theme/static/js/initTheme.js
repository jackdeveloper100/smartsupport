// const body = document.body;
// const theme = localStorage.getItem('theme')

// if (theme) 
//   document.documentElement.setAttribute('data-bs-theme', theme)

// Force theme to light after login
// document.documentElement.setAttribute('data-bs-theme', 'light');
    
// Optional: Remove old saved theme from storage
// localStorage.removeItem('theme');

// const body = document.body;
// const theme = localStorage.getItem('theme')

// if (theme) 
//   document.documentElement.setAttribute('data-bs-theme', theme)

// Force light theme no matter what
// localStorage.setItem('theme', 'light');
// document.documentElement.setAttribute('data-bs-theme', 'light');


  document.addEventListener('DOMContentLoaded', function () {
  const themeToggle = document.getElementById('toggle-dark');
  const logoutBtn = document.getElementById('logout-btn');
  const logoutBtn1 = document.getElementById('logout-btn1');
  
  if (!themeToggle) return; // optional safety check

  // Set default theme
  if (!localStorage.getItem('theme')) {
    localStorage.setItem('theme', 'light');
  }

  // Apply saved theme
  const savedTheme = localStorage.getItem('theme');
  document.documentElement.setAttribute('data-bs-theme', savedTheme);

  // Set checkbox based on theme
  themeToggle.checked = savedTheme === 'dark';

  // Theme change handler
  themeToggle.addEventListener('change', function () {
    const newTheme = this.checked ? 'dark' : 'light';
    localStorage.setItem('theme', newTheme);
    document.documentElement.setAttribute('data-bs-theme', newTheme);
  });

  // Logout button
  logoutBtn.addEventListener('click', function () {
    localStorage.removeItem('theme');
        location.reload();
  });
  
   if (logoutBtn1) {
      logoutBtn1.addEventListener('click', function () {
        localStorage.removeItem('theme');
        location.reload();
      });
   }
});



