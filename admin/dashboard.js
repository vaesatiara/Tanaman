import { Chart } from "@/components/ui/chart"
import { jsPDF } from "jspdf"

// Dashboard JavaScript
document.addEventListener("DOMContentLoaded", () => {
  // Initialize Charts
  initializeSalesChart()
  initializeCategoryChart()

  // Initialize other features
  initializeSearch()
  initializeNotifications()
  initializeResponsiveMenu()
  generateMonthlyReportButton()
})

// Sales Chart
function initializeSalesChart() {
  const salesCtx = document.getElementById("salesChart")?.getContext("2d")
  if (!salesCtx) return

  new Chart(salesCtx, {
    type: "line",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
      datasets: [
        {
          label: "Penjualan (Juta Rupiah)",
          data: [12, 19, 15, 25, 22, 30],
          borderColor: "#8ed7a9",
          backgroundColor: "rgba(142, 215, 169, 0.1)",
          tension: 0.4,
          fill: true,
          pointBackgroundColor: "#8ed7a9",
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
          borderColor: "#8ed7a9",
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
  const categoryCtx = document.getElementById("categoryChart")?.getContext("2d")
  if (!categoryCtx) return

  new Chart(categoryCtx, {
    type: "doughnut",
    data: {
      labels: ["Tanaman Hias Daun", "Tanaman Hias Bunga", "Pot & Aksesoris"],
      datasets: [
        {
          data: [60, 30, 10],
          backgroundColor: ["#8ed7a9", "#ffb6c1", "#b5c8e0"],
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
          borderColor: "#8ed7a9",
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
  if (!searchInput) return

  searchInput.addEventListener("input", (e) => {
    const searchTerm = e.target.value.toLowerCase()
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
  if (!notificationBtn) return

  notificationBtn.addEventListener("click", () => {
    showToast("Anda memiliki 3 notifikasi baru!", "info")
  })
}

// Responsive menu
function initializeResponsiveMenu() {
  // Handle window resize
  window.addEventListener("resize", () => {
    const sidebar = document.getElementById("sidebar")
    if (window.innerWidth > 768) {
      sidebar.classList.remove("show")
    }
  })
}

// Toggle sidebar for mobile
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar")
  sidebar.classList.toggle("show")
}

// Toast notification system
function showToast(message, type = "info") {
  const toast = document.getElementById("toast")
  const icon = document.querySelector(".toast-icon-element")
  const messageEl = document.querySelector(".toast-message")

  messageEl.textContent = message
  toast.className = `toast show ${type}`

  if (type === "success") {
    icon.className = "toast-icon-element fas fa-check-circle"
  } else if (type === "error") {
    icon.className = "toast-icon-element fas fa-exclamation-circle"
  } else if (type === "info") {
    icon.className = "toast-icon-element fas fa-info-circle"
  }

  setTimeout(() => {
    closeToast()
  }, 4000)
}

function closeToast() {
  const toast = document.getElementById("toast")
  toast.classList.remove("show")
}

// Monthly Report Generation
function generateMonthlyReport() {
  const reportBtn = document.querySelector(".btn-report")

  // Add loading state
  reportBtn.classList.add("loading")

  // Show progress toast
  showToast("Memproses laporan bulanan...", "info")

  // Simulate processing time
  setTimeout(() => {
    try {
      // Create PDF using jsPDF
      const doc = new jsPDF()

      // Get current date
      const currentDate = new Date()
      const monthNames = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
      ]
      const currentMonth = monthNames[currentDate.getMonth()]
      const currentYear = currentDate.getFullYear()

      // PDF Header
      doc.setFontSize(20)
      doc.setFont("helvetica", "bold")
      doc.text("LAPORAN BULANAN", 105, 20, { align: "center" })

      doc.setFontSize(16)
      doc.setFont("helvetica", "normal")
      doc.text("The Secret Garden", 105, 30, { align: "center" })

      doc.setFontSize(12)
      doc.text(`Periode: ${currentMonth} ${currentYear}`, 105, 40, { align: "center" })
      doc.text(`Tanggal Cetak: ${currentDate.toLocaleDateString("id-ID")}`, 105, 50, { align: "center" })

      // Line separator
      doc.setLineWidth(0.5)
      doc.line(20, 55, 190, 55)

      // Summary Statistics
      doc.setFontSize(14)
      doc.setFont("helvetica", "bold")
      doc.text("RINGKASAN STATISTIK", 20, 70)

      doc.setFontSize(10)
      doc.setFont("helvetica", "normal")

      const stats = [
        { label: "Total Pesanan", value: "1,250", growth: "+12%" },
        { label: "Total Customer", value: "890", growth: "+8%" },
        { label: "Total Produk", value: "156", growth: "+5%" },
        { label: "Total Revenue", value: "Rp 45,231,890", growth: "+15%" },
      ]

      let yPos = 80
      stats.forEach((stat, index) => {
        doc.text(`${stat.label}:`, 25, yPos)
        doc.text(stat.value, 80, yPos)
        doc.setTextColor(0, 128, 0)
        doc.text(stat.growth, 140, yPos)
        doc.setTextColor(0, 0, 0)
        yPos += 10
      })

      // Recent Orders Section
      yPos += 10
      doc.setFontSize(14)
      doc.setFont("helvetica", "bold")
      doc.text("PESANAN TERBARU", 20, yPos)

      yPos += 10
      doc.setFontSize(9)
      doc.setFont("helvetica", "bold")

      // Table headers
      doc.text("ID", 25, yPos)
      doc.text("Customer", 45, yPos)
      doc.text("Produk", 85, yPos)
      doc.text("Total", 125, yPos)
      doc.text("Status", 155, yPos)

      // Table data
      const orders = [
        {
          id: "#ORD-001",
          customer: "Budi Santoso",
          product: "Monstera Deliciosa",
          total: "Rp 250,000",
          status: "Selesai",
        },
        { id: "#ORD-002", customer: "Sari Dewi", product: "Fiddle Leaf Fig", total: "Rp 180,000", status: "Proses" },
        { id: "#ORD-003", customer: "Ahmad Rahman", product: "Snake Plant", total: "Rp 120,000", status: "Dikirim" },
        { id: "#ORD-004", customer: "Maya Putri", product: "Peace Lily", total: "Rp 95,000", status: "Pending" },
        { id: "#ORD-005", customer: "Rizki Pratama", product: "Rubber Plant", total: "Rp 150,000", status: "Selesai" },
      ]

      doc.setFont("helvetica", "normal")
      yPos += 5

      orders.forEach((order, index) => {
        yPos += 8
        doc.text(order.id, 25, yPos)
        doc.text(order.customer, 45, yPos)
        doc.text(order.product, 85, yPos)
        doc.text(order.total, 125, yPos)
        doc.text(order.status, 155, yPos)
      })

      // Top Products Section
      yPos += 20
      doc.setFontSize(14)
      doc.setFont("helvetica", "bold")
      doc.text("PRODUK TERLARIS", 20, yPos)

      yPos += 10
      doc.setFontSize(9)
      doc.setFont("helvetica", "bold")

      // Table headers
      doc.text("Rank", 25, yPos)
      doc.text("Produk", 45, yPos)
      doc.text("Kategori", 85, yPos)
      doc.text("Terjual", 125, yPos)
      doc.text("Revenue", 155, yPos)

      // Table data
      const topProducts = [
        {
          rank: "#1",
          product: "Monstera Deliciosa",
          category: "Tanaman Hias Daun",
          sold: "85 unit",
          revenue: "Rp 21,250,000",
        },
        {
          rank: "#2",
          product: "Fiddle Leaf Fig",
          category: "Tanaman Hias Daun",
          sold: "72 unit",
          revenue: "Rp 12,960,000",
        },
        { rank: "#3", product: "Snake Plant", category: "Tanaman Hias Daun", sold: "68 unit", revenue: "Rp 8,160,000" },
        { rank: "#4", product: "Peace Lily", category: "Tanaman Hias Bunga", sold: "54 unit", revenue: "Rp 5,130,000" },
        {
          rank: "#5",
          product: "Rubber Plant",
          category: "Tanaman Hias Daun",
          sold: "48 unit",
          revenue: "Rp 7,200,000",
        },
      ]

      doc.setFont("helvetica", "normal")
      yPos += 5

      topProducts.forEach((product, index) => {
        yPos += 8
        doc.text(product.rank, 25, yPos)
        doc.text(product.product, 45, yPos)
        doc.text(product.category, 85, yPos)
        doc.text(product.sold, 125, yPos)
        doc.text(product.revenue, 155, yPos)
      })

      // Footer
      yPos = 280
      doc.setFontSize(8)
      doc.setTextColor(128, 128, 128)
      doc.text("Laporan ini dibuat secara otomatis oleh sistem The Secret Garden", 105, yPos, { align: "center" })
      doc.text(`© ${currentYear} The Secret Garden. All rights reserved.`, 105, yPos + 5, { align: "center" })

      // Save the PDF
      const fileName = `Laporan_Bulanan_${currentMonth}_${currentYear}.pdf`
      doc.save(fileName)

      // Remove loading state
      reportBtn.classList.remove("loading")

      // Show success message
      showToast(`Laporan bulanan berhasil diunduh: ${fileName}`, "success")
    } catch (error) {
      console.error("Error generating PDF:", error)
      reportBtn.classList.remove("loading")
      showToast("Gagal membuat laporan. Silakan coba lagi.", "error")
    }
  }, 2000) // 2 second delay to simulate processing
}

function generateMonthlyReportButton() {
  const reportBtn = document.querySelector(".btn-report")
  if (!reportBtn) return

  reportBtn.addEventListener("click", generateMonthlyReport)
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
    console.log("Auto-refreshing data...")
    // You can implement AJAX calls here to update data
  }, 300000) // Refresh every 5 minutes
}

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

// Close sidebar when clicking outside on mobile
document.addEventListener("click", (event) => {
  const sidebar = document.getElementById("sidebar")
  const menuBtn = document.querySelector(".mobile-menu-btn")

  if (
    window.innerWidth <= 768 &&
    !sidebar.contains(event.target) &&
    !menuBtn.contains(event.target) &&
    sidebar.classList.contains("show")
  ) {
    sidebar.classList.remove("show")
  }
})
