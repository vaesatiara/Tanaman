x   // Sample order data
const sampleOrders = [
  {
    id_pesanan: 1,
    nomor_pesanan: "TSG001",
    nama_pelanggan: "John Doe",
    email_pelanggan: "john@example.com",
    alamat_pelanggan: "Jl. Sudirman No. 123, Jakarta",
    telp_pelanggan: "081234567890",
    tgl_pesanan: "2024-01-15T10:30:00",
    total: 325000,
    status_pesanan: "menunggu konfirmasi",
    items: [
      {
        id_produk: 1,
        nama_produk: "Monstera Deliciosa",
        kategori: "Tanaman Hias Daun",
        harga: 250000,
        jumlah: 1,
        subtotal: 250000,
        gambar: "monstera.jpg",
        deskripsi: "Tanaman hias daun dengan bentuk unik berlubang",
      },
      {
        id_produk: 2,
        nama_produk: "Pot Keramik Premium",
        kategori: "Aksesoris",
        harga: 75000,
        jumlah: 1,
        subtotal: 75000,
        gambar: "pot-keramik.jpg",
        deskripsi: "Pot keramik berkualitas tinggi dengan desain modern",
      },
    ],
  },
  {
    id_pesanan: 2,
    nomor_pesanan: "TSG002",
    nama_pelanggan: "Jane Smith",
    email_pelanggan: "jane@example.com",
    alamat_pelanggan: "Jl. Thamrin No. 456, Jakarta",
    telp_pelanggan: "081234567891",
    tgl_pesanan: "2024-01-14T14:20:00",
    total: 180000,
    status_pesanan: "diproses",
    items: [
      {
        id_produk: 3,
        nama_produk: "Fiddle Leaf Fig",
        kategori: "Tanaman Hias Daun",
        harga: 180000,
        jumlah: 1,
        subtotal: 180000,
        gambar: "fiddle-leaf.jpg",
        deskripsi: "Tanaman hias dengan daun besar dan mengkilap",
      },
    ],
  },
  {
    id_pesanan: 3,
    nomor_pesanan: "TSG003",
    nama_pelanggan: "Bob Wilson",
    email_pelanggan: "bob@example.com",
    alamat_pelanggan: "Jl. Gatot Subroto No. 789, Jakarta",
    telp_pelanggan: "081234567892",
    tgl_pesanan: "2024-01-13T09:15:00",
    total: 170000,
    status_pesanan: "dikirim",
    items: [
      {
        id_produk: 4,
        nama_produk: "Snake Plant",
        kategori: "Tanaman Hias Daun",
        harga: 120000,
        jumlah: 1,
        subtotal: 120000,
        gambar: "snake-plant.jpg",
        deskripsi: "Tanaman hias yang mudah perawatan dan tahan lama",
      },
      {
        id_produk: 5,
        nama_produk: "Pupuk Organik",
        kategori: "Perawatan",
        harga: 25000,
        jumlah: 2,
        subtotal: 50000,
        gambar: "pupuk.jpg",
        deskripsi: "Pupuk organik untuk pertumbuhan optimal tanaman",
      },
    ],
  },
  {
    id_pesanan: 4,
    nomor_pesanan: "TSG004",
    nama_pelanggan: "Alice Johnson",
    email_pelanggan: "alice@example.com",
    alamat_pelanggan: "Jl. Kuningan No. 321, Jakarta",
    telp_pelanggan: "081234567893",
    tgl_pesanan: "2024-01-12T16:45:00",
    total: 450000,
    status_pesanan: "selesai",
    items: [
      {
        id_produk: 6,
        nama_produk: "Peace Lily",
        kategori: "Tanaman Hias Bunga",
        harga: 200000,
        jumlah: 2,
        subtotal: 400000,
        gambar: "peace-lily.jpg",
        deskripsi: "Tanaman hias berbunga putih yang elegan",
      },
      {
        id_produk: 7,
        nama_produk: "Spray Bottle",
        kategori: "Aksesoris",
        harga: 25000,
        jumlah: 2,
        subtotal: 50000,
        gambar: "spray-bottle.jpg",
        deskripsi: "Botol semprot untuk perawatan tanaman",
      },
    ],
  },
]

const statusOptions = [
  { value: "menunggu konfirmasi", label: "Menunggu Konfirmasi" },
  { value: "dikonfirmasi", label: "Dikonfirmasi" },
  { value: "diproses", label: "Diproses" },
  { value: "dikemas", label: "Dikemas" },
  { value: "menunggu dikirim", label: "Menunggu Dikirim" },
  { value: "dikirim", label: "Dikirim" },
  { value: "selesai", label: "Selesai" },
  { value: "dibatalkan", label: "Dibatalkan" },
]

// Global variables
const orders = [...sampleOrders]
let currentStatusUpdate = null

// DOM Elements
const mobileMenuBtn = document.getElementById("mobileMenuBtn")
const sidebar = document.getElementById("sidebar")
const sidebarOverlay = document.getElementById("sidebarOverlay")
const searchInput = document.getElementById("searchInput")
const ordersTableBody = document.getElementById("ordersTableBody")

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  initializeApp()
})

function initializeApp() {
  setupEventListeners()
  renderOrders()
  updateStats()
}

function setupEventListeners() {
  // Mobile menu toggle
  mobileMenuBtn.addEventListener("click", toggleSidebar)
  sidebarOverlay.addEventListener("click", closeSidebar)

  // Search functionality
  searchInput.addEventListener("input", handleSearch)

  // Modal close buttons
  document.querySelectorAll(".modal-close").forEach((btn) => {
    btn.addEventListener("click", function () {
      const modalId = this.getAttribute("data-modal")
      closeModal(modalId)
    })
  })

  // Close modal when clicking outside
  document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", function (e) {
      if (e.target === this) {
        closeModal(this.id)
      }
    })
  })

  // Close sidebar when clicking on menu items (mobile)
  document.querySelectorAll(".menu-item").forEach((item) => {
    item.addEventListener("click", () => {
      if (window.innerWidth <= 768) {
        closeSidebar()
      }
    })
  })
}

function toggleSidebar() {
  sidebar.classList.toggle("show")
  sidebarOverlay.classList.toggle("show")

  // Update mobile menu button icon
  const icon = mobileMenuBtn.querySelector("i")
  if (sidebar.classList.contains("show")) {
    icon.className = "fas fa-times"
  } else {
    icon.className = "fas fa-bars"
  }
}

function closeSidebar() {
  sidebar.classList.remove("show")
  sidebarOverlay.classList.remove("show")

  // Reset mobile menu button icon
  const icon = mobileMenuBtn.querySelector("i")
  icon.className = "fas fa-bars"
}

function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase()
  const filteredOrders = orders.filter(
    (order) =>
      order.nomor_pesanan.toLowerCase().includes(searchTerm) ||
      order.nama_pelanggan.toLowerCase().includes(searchTerm) ||
      order.email_pelanggan.toLowerCase().includes(searchTerm),
  )
  renderOrders(filteredOrders)
}

function renderOrders(ordersToRender = orders) {
  const tbody = ordersTableBody
  tbody.innerHTML = ""

  ordersToRender.forEach((order) => {
    const row = createOrderRow(order)
    tbody.appendChild(row)
  })

  // Add animation to new rows
  setTimeout(() => {
    tbody.querySelectorAll("tr").forEach((row, index) => {
      row.style.animation = `fadeInUp 0.3s ease ${index * 0.1}s both`
    })
  }, 10)
}

function createOrderRow(order) {
  const row = document.createElement("tr")
  row.setAttribute("data-order-id", order.id_pesanan)

  row.innerHTML = `
    <td>
      <span class="font-semibold">#${order.nomor_pesanan}</span>
    </td>
    <td>
      <div class="customer-info">
        <div class="customer-name">${order.nama_pelanggan}</div>
        <div class="customer-email">${order.email_pelanggan}</div>
      </div>
    </td>
    <td>${formatDate(order.tgl_pesanan)}</td>
    <td>
      <span class="font-semibold">${formatCurrency(order.total)}</span>
    </td>
    <td>
      <span class="status-badge status-${order.status_pesanan.replace(/\s+/g, "-")}">
        ${getStatusLabel(order.status_pesanan)}
      </span>
    </td>
    <td>
      <select class="status-select" data-order-id="${order.id_pesanan}" onchange="handleStatusChange(this)">
        ${statusOptions
          .map(
            (option) =>
              `<option value="${option.value}" ${option.value === order.status_pesanan ? "selected" : ""}>
            ${option.label}
          </option>`,
          )
          .join("")}
      </select>
    </td>
    <td>
      <div class="action-buttons">
        <button class="btn-action btn-detail" onclick="showOrderDetail(${order.id_pesanan})" title="Lihat Detail">
          <i class="fas fa-eye"></i>
        </button>
        <button class="btn-action btn-items" onclick="showOrderItems(${order.id_pesanan})" title="Detail Items">
          <i class="fas fa-list-ul"></i>
        </button>
      </div>
    </td>
  `

  return row
}

function updateStats() {
  const stats = {
    total: orders.length,
    pending: orders.filter((o) => o.status_pesanan === "menunggu konfirmasi").length,
    processing: orders.filter((o) => o.status_pesanan === "diproses").length,
    shipped: orders.filter((o) => o.status_pesanan === "dikirim").length,
  }

  // Animate counter updates
  animateCounter("totalOrders", stats.total)
  animateCounter("pendingOrders", stats.pending)
  animateCounter("processingOrders", stats.processing)
  animateCounter("shippedOrders", stats.shipped)
}

function animateCounter(elementId, targetValue) {
  const element = document.getElementById(elementId)
  const currentValue = Number.parseInt(element.textContent) || 0
  const increment = targetValue > currentValue ? 1 : -1
  const duration = 1000
  const steps = Math.abs(targetValue - currentValue)
  const stepDuration = duration / steps

  let current = currentValue

  const timer = setInterval(() => {
    current += increment
    element.textContent = current

    if (current === targetValue) {
      clearInterval(timer)
    }
  }, stepDuration)
}

function handleStatusChange(selectElement) {
  const orderId = Number.parseInt(selectElement.getAttribute("data-order-id"))
  const newStatus = selectElement.value
  const order = orders.find((o) => o.id_pesanan === orderId)

  if (order && order.status_pesanan !== newStatus) {
    currentStatusUpdate = {
      orderId: orderId,
      oldStatus: order.status_pesanan,
      newStatus: newStatus,
      selectElement: selectElement,
    }

    const statusText = `Status akan diubah dari "<strong>${getStatusLabel(order.status_pesanan)}</strong>" menjadi "<strong>${getStatusLabel(newStatus)}</strong>"`
    document.getElementById("statusChangeText").innerHTML = statusText
    document.getElementById("statusNote").value = ""

    showModal("statusConfirmModal")
  }
}

function confirmStatusUpdate() {
  if (!currentStatusUpdate) return

  const { orderId, newStatus, selectElement } = currentStatusUpdate
  const note = document.getElementById("statusNote").value

  // Update order status in data
  const orderIndex = orders.findIndex((o) => o.id_pesanan === orderId)
  if (orderIndex !== -1) {
    orders[orderIndex].status_pesanan = newStatus
  }

  // Update status badge in table
  const row = document.querySelector(`tr[data-order-id="${orderId}"]`)
  if (row) {
    const statusBadge = row.querySelector(".status-badge")
    statusBadge.className = `status-badge status-${newStatus.replace(/\s+/g, "-")}`
    statusBadge.textContent = getStatusLabel(newStatus)

    // Add update animation
    statusBadge.style.animation = "pulse 0.5s ease"
    setTimeout(() => {
      statusBadge.style.animation = ""
    }, 500)
  }

  // Update stats
  updateStats()

  // Show success toast
  showToast(`Status pesanan berhasil diubah menjadi "${getStatusLabel(newStatus)}"!`, "success")

  // Close modal and reset
  closeModal("statusConfirmModal")
  currentStatusUpdate = null
}

function cancelStatusUpdate() {
  if (currentStatusUpdate) {
    // Reset select element to original value
    currentStatusUpdate.selectElement.value = currentStatusUpdate.oldStatus
    currentStatusUpdate = null
  }
  closeModal("statusConfirmModal")
}

function showOrderDetail(orderId) {
  const order = orders.find((o) => o.id_pesanan === orderId)
  if (!order) {
    showToast("Data pesanan tidak ditemukan!", "error")
    return
  }

  const content = `
    <div class="order-detail-grid">
      <div class="detail-section">
        <h4><i class="fas fa-info-circle"></i> Informasi Pesanan</h4>
        <div class="detail-item">
          <label>Nomor Pesanan:</label>
          <span class="font-semibold">#${order.nomor_pesanan}</span>
        </div>
        <div class="detail-item">
          <label>Tanggal Pesanan:</label>
          <span>${formatDate(order.tgl_pesanan)}</span>
        </div>
        <div class="detail-item">
          <label>Status:</label>
          <span class="status-badge status-${order.status_pesanan.replace(/\s+/g, "-")}">
            ${getStatusLabel(order.status_pesanan)}
          </span>
        </div>
        <div class="detail-item">
          <label>Total Pembayaran:</label>
          <span class="total-amount">${formatCurrency(order.total)}</span>
        </div>
      </div>

      <div class="detail-section">
        <h4><i class="fas fa-user"></i> Informasi Customer</h4>
        <div class="detail-item">
          <label>Nama:</label>
          <span class="font-semibold">${order.nama_pelanggan}</span>
        </div>
        <div class="detail-item">
          <label>Email:</label>
          <span>${order.email_pelanggan}</span>
        </div>
        <div class="detail-item">
          <label>No. Telepon:</label>
          <span>${order.telp_pelanggan}</span>
        </div>
        <div class="detail-item">
          <label>Alamat:</label>
          <span class="text-sm">${order.alamat_pelanggan}</span>
        </div>
      </div>
    </div>
  `

  document.getElementById("orderDetailContent").innerHTML = content
  showModal("orderDetailModal")
}

function showOrderItems(orderId) {
  const order = orders.find((o) => o.id_pesanan === orderId)
  if (!order) {
    showToast("Data pesanan tidak ditemukan!", "error")
    return
  }

  if (!order.items || order.items.length === 0) {
    showToast("Tidak ada item dalam pesanan ini!", "warning")
    return
  }

  const totalItems = order.items.reduce((sum, item) => sum + item.jumlah, 0)
  const subtotalProducts = order.items.reduce((sum, item) => sum + item.subtotal, 0)
  const shippingCost = order.total - subtotalProducts

  let itemsHtml = ""
  order.items.forEach((item) => {
    itemsHtml += `
      <div class="item-card">
        <div class="item-image">
          <img src="/placeholder.svg?height=64&width=64" alt="${item.nama_produk}" />
        </div>
        <div class="item-details">
          <div class="item-header">
            <div>
              <h5 class="item-name">${item.nama_produk}</h5>
              <span class="item-category">${item.kategori}</span>
            </div>
          </div>
          <p class="text-sm text-gray-600 mb-2">${item.deskripsi || "Tidak ada deskripsi"}</p>
          <div class="item-pricing">
            <div class="price-info">
              <span>${formatCurrency(item.harga)}</span>
              <span>x ${item.jumlah}</span>
            </div>
            <div class="subtotal font-semibold">
              ${formatCurrency(item.subtotal)}
            </div>
          </div>
        </div>
      </div>
    `
  })

  const content = `
    <div class="order-summary">
      <div class="summary-item">
        <span>Pesanan:</span>
        <span class="font-semibold">#${order.nomor_pesanan}</span>
      </div>
      <div class="summary-item">
        <span>Customer:</span>
        <span class="font-semibold">${order.nama_pelanggan}</span>
      </div>
      <div class="summary-item">
        <span>Total Items:</span>
        <span class="font-semibold">${totalItems} item</span>
      </div>
      <div class="summary-item">
        <span>Status:</span>
        <span class="status-badge status-${order.status_pesanan.replace(/\s+/g, "-")}">
          ${getStatusLabel(order.status_pesanan)}
        </span>
      </div>
    </div>

    <div class="items-list">
      <h4><i class="fas fa-shopping-bag"></i> Daftar Item (${order.items.length} produk)</h4>
      ${itemsHtml}
    </div>

    <div class="order-total">
      <div class="total-breakdown">
        <div class="total-item">
          <span>Subtotal Produk:</span>
          <span>${formatCurrency(subtotalProducts)}</span>
        </div>
        <div class="total-item">
          <span>Ongkos Kirim:</span>
          <span>${formatCurrency(shippingCost)}</span>
        </div>
        <div class="total-item grand-total">
          <span><strong>Total Pembayaran:</strong></span>
          <span><strong>${formatCurrency(order.total)}</strong></span>
        </div>
      </div>
    </div>
  `

  document.getElementById("orderItemsContent").innerHTML = content
  showModal("orderItemsModal")
}

function showModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.add("show")
    document.body.style.overflow = "hidden"

    // Add animation
    setTimeout(() => {
      const modalContent = modal.querySelector(".modal-content")
      if (modalContent) {
        modalContent.style.animation = "modalSlideIn 0.3s ease"
      }
    }, 10)
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.remove("show")
    document.body.style.overflow = ""
  }
}

function showToast(message, type = "info") {
  const toast = document.getElementById("toast")
  const icon = document.querySelector(".toast-icon-element")
  const messageEl = document.querySelector(".toast-message")

  messageEl.textContent = message
  toast.className = `toast show ${type}`

  // Set appropriate icon
  switch (type) {
    case "success":
      icon.className = "toast-icon-element fas fa-check-circle"
      break
    case "error":
      icon.className = "toast-icon-element fas fa-exclamation-circle"
      break
    case "warning":
      icon.className = "toast-icon-element fas fa-exclamation-triangle"
      break
    default:
      icon.className = "toast-icon-element fas fa-info-circle"
  }

  // Auto hide after 4 seconds
  setTimeout(() => {
    closeToast()
  }, 4000)
}

function closeToast() {
  const toast = document.getElementById("toast")
  toast.classList.remove("show")
}

// Utility functions
function formatCurrency(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(amount)
}

function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

function getStatusLabel(status) {
  const option = statusOptions.find((opt) => opt.value === status)
  return option ? option.label : status
}

// Add CSS animations
const style = document.createElement("style")
style.textContent = `
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
  }
  
  @keyframes modalSlideIn {
    from {
      opacity: 0;
      transform: translateY(-50px) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }
`
document.head.appendChild(style)

// Handle window resize
window.addEventListener("resize", () => {
  if (window.innerWidth > 768) {
    closeSidebar()
  }
})

// Handle escape key to close modals
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    // Close any open modals
    document.querySelectorAll(".modal.show").forEach((modal) => {
      closeModal(modal.id)
    })

    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
      closeSidebar()
    }

    // Close toast
    closeToast()
  }
})
