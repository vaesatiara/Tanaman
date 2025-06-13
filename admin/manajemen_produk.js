// Toggle mobile sidebar
document.addEventListener("DOMContentLoaded", () => {
  // Add mobile menu button if it doesn't exist
  if (!document.querySelector(".mobile-menu-btn")) {
    const mobileBtn = document.createElement("button")
    mobileBtn.className = "mobile-menu-btn"
    mobileBtn.innerHTML = '<i class="fas fa-bars"></i>'
    document.body.appendChild(mobileBtn)

    mobileBtn.addEventListener("click", () => {
      document.querySelector(".sidebar").classList.toggle("show")
    })
  }

  // Close sidebar when clicking outside
  document.addEventListener("click", (e) => {
    const sidebar = document.querySelector(".sidebar")
    const mobileBtn = document.querySelector(".mobile-menu-btn")

    if (sidebar && mobileBtn && !sidebar.contains(e.target) && !mobileBtn.contains(e.target)) {
      sidebar.classList.remove("show")
    }
  })

  // Create image preview modal
  createImageModal()

  // Add click event to all product images
  const productImages = document.querySelectorAll(".table td img")
  productImages.forEach((img) => {
    img.addEventListener("click", function () {
      showImageModal(this.src, this.alt)
    })
  })
})

// Create modal for image preview
function createImageModal() {
  const modal = document.createElement("div")
  modal.className = "modal"
  modal.id = "imageModal"
  modal.innerHTML = `
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <img class="modal-image" id="modalImage" src="/placeholder.svg" alt="">
        </div>
    `
  document.body.appendChild(modal)

  // Close modal when clicking on X
  document.querySelector(".modal-close").addEventListener("click", () => {
    document.getElementById("imageModal").style.display = "none"
  })

  // Close modal when clicking outside the image
  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none"
    }
  })
}

// Show image in modal
function showImageModal(src, alt) {
  const modal = document.getElementById("imageModal")
  const modalImg = document.getElementById("modalImage")

  modal.style.display = "block"
  modalImg.src = src
  modalImg.alt = alt || "Product Image"
}

// Toggle text expansion for long descriptions
function toggleText(button) {
  const textDiv = button.previousElementSibling
  if (textDiv.classList.contains("truncate-text")) {
    textDiv.classList.remove("truncate-text")
    button.textContent = "Sembunyikan"
  } else {
    textDiv.classList.add("truncate-text")
    button.textContent = "Lihat Selengkapnya"
  }
}
