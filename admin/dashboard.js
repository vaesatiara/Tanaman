import { Chart } from "@/components/ui/chart"
// Dashboard JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize Charts
  initializeSalesChart()
  initializeCategoryChart()

  // Initialize other features
  initializeSearch()
  initializeNotifications()
  initializeResponsiveMenu()
})

// Sales Chart
function initializeSalesChart() {
  const salesCtx = document.getElementById("salesChart").getContext("2d")

  new Chart(salesCtx, {
    type: "line",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
      datasets: [
        {
          label: "Penjualan (Juta Rupiah)",
          data: [12, 19, 15, 25, 22, 30],
          borderColor: "#667eea",
          backgroundColor: "rgba(102, 126, 234, 0.1)",
          tension: 0.4,
          fill: true,
          pointBackgroundColor: "#667eea",
          pointBorderColor: "#ffffff",
          pointBorderWidth: 2,
          pointRadius: 6,
          pointHoverRadius: 8,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: "rgba(0, 0, 0, 0.8)",
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          borderColor: "#667eea",
          borderWidth: 1,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            label: (context) => "Penjualan: Rp " + context.parsed.y + " Juta",
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "rgba(0,0,0,0.1)",
            drawBorder: false,
          },
          ticks: {
            color: "#4a5568",
            callback: (value) => "Rp " + value + "M",
          },
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            color: "#4a5568",
          },
        },
      },
      interaction: {
        intersect: false,
        mode: "index",
      },
    },
  })
}

// Category Chart
function initializeCategoryChart() {
  const categoryCtx = document.getElementById("categoryChart").getContext("2d")

  new Chart(categoryCtx, {
    type: "doughnut",
    data: {
      labels: ["Tanaman Hias Daun", "Tanaman Hias Bunga", "Pot & Aksesoris"],
      datasets: [
        {
          data: [60, 30, 10],
          backgroundColor: ["#667eea", "#764ba2", "#f093fb"],
          borderWidth: 0,
          hoverOffset: 10,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            padding: 20,
            usePointStyle: true,
            font: {
              size: 12,
              weight: "500",
            },
            color: "#4a5568",
          },
        },
        tooltip: {
          backgroundColor: "rgba(0, 0, 0, 0.8)",
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          borderColor: "#667eea",
          borderWidth: 1,
          cornerRadius: 8,
          callbacks: {
            label: (context) => context.label + ": " + context.parsed + "%",
          },
        },
      },
      cutout: "60%",
    },
  })
}

// Search functionality
function initializeSearch() {
  const searchInput = document.querySelector(".search-input")

  searchInput.addEventListener("input", (e) => {
    const searchTerm = e.target.value.toLowerCase()

    // Add search functionality here
    console.log("Searching for:", searchTerm)

    // You can implement table filtering here
    filterTables(searchTerm)
  })
}

// Filter tables based on search term
function filterTables(searchTerm) {
  const tables = document.querySelectorAll("table tbody tr")

  tables.forEach((row) => {
    const text = row.textContent.toLowerCase()
    if (text.includes(searchTerm)) {
      row.style.display = ""
    } else {
      row.style.display = "none"
    }
  })
}

// Notification functionality
function initializeNotifications() {
  const notificationBtn = document.querySelector(".notification")

  notificationBtn.addEventListener("click", () => {
    // Add notification dropdown or modal here
    alert("Anda memiliki 3 notifikasi baru!")
  })
}

// Responsive menu
function initializeResponsiveMenu() {
  // Add mobile menu toggle functionality
  const sidebar = document.querySelector(".sidebar")
  const mainContent = document.querySelector(".main-content")

  // Create mobile menu button
  if (window.innerWidth <= 768) {
    createMobileMenuButton()
  }

  window.addEventListener("resize", () => {
    if (window.innerWidth <= 768) {
      createMobileMenuButton()
    } else {
      removeMobileMenuButton()
    }
  })
}

function createMobileMenuButton() {
  if (document.querySelector(".mobile-menu-btn")) return

  const menuBtn = document.createElement("button")
  menuBtn.className = "mobile-menu-btn"
  menuBtn.innerHTML = '<i class="fas fa-bars"></i>'
  menuBtn.style.cssText = `
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: #667eea;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `

  document.body.appendChild(menuBtn)

  menuBtn.addEventListener("click", () => {
    const sidebar = document.querySelector(".sidebar")
    sidebar.style.display = sidebar.style.display === "none" ? "block" : "none"
  })
}

function removeMobileMenuButton() {
  const menuBtn = document.querySelector(".mobile-menu-btn")
  if (menuBtn) {
    menuBtn.remove()
  }
}

// Utility functions
function formatCurrency(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(amount)
}

function formatNumber(number) {
  return new Intl.NumberFormat("id-ID").format(number)
}

// Auto-refresh data (optional)
function autoRefreshData() {
  setInterval(() => {
    // Refresh data from server
    console.log("Auto-refreshing data...")
    // You can implement AJAX calls here to update data
  }, 300000) // Refresh every 5 minutes
}

// Initialize auto-refresh (uncomment if needed)
// autoRefreshData();

// Smooth scrolling for internal links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()
    const target = document.querySelector(this.getAttribute("href"))
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      })
    }
  })
})

// Add loading states for buttons
document.querySelectorAll("button").forEach((button) => {
  button.addEventListener("click", function () {
    if (!this.classList.contains("loading")) {
      this.classList.add("loading")
      this.innerHTML = '<span class="loading"></span> Loading...'

      // Remove loading state after 2 seconds (adjust as needed)
      setTimeout(() => {
        this.classList.remove("loading")
        this.innerHTML = this.getAttribute("data-original-text") || "Button"
      }, 2000)
    }
  })
})
