// JavaScript untuk menangani fungsi alamat tersimpan
document.addEventListener("DOMContentLoaded", () => {
  // Data alamat (simulasi - dalam aplikasi nyata ini dari database)
  const addressData = [
    {
      id: 1,
      label: "Rumah",
      icon: "fas fa-home",
      name: "Caselline",
      phone: "0812-3456-7890",
      province: "jateng",
      city: "purwokerto",
      district: "maju-jaya",
      postalCode: "12345",
      fullAddress: "Jl. Tanaman Indah No. 123",
      isPrimary: true,
    },
    {
      id: 2,
      label: "Kantor",
      icon: "fas fa-building",
      name: "Caselline",
      phone: "0812-3456-7890",
      province: "jateng",
      city: "purbalingga",
      district: "sukses",
      postalCode: "10110",
      fullAddress: "Jl. Bisnis Raya No. 45",
      isPrimary: false,
    },
    {
      id: 3,
      label: "Rumah Orang Tua",
      icon: "fas fa-heart",
      name: "Bapak Caselline",
      phone: "0813-7890-1234",
      province: "jateng",
      city: "banyumas",
      district: "harmoni",
      postalCode: "53100",
      fullAddress: "Jl. Keluarga Bahagia No. 67",
      isPrimary: false,
    },
  ]

  // Elemen DOM
  const addAddressBtn = document.getElementById("addAddressBtn")
  const addressFormContainer = document.getElementById("addressFormContainer")
  const closeFormBtn = document.getElementById("closeFormBtn")
  const cancelBtn = document.getElementById("cancelBtn")
  const addressForm = document.getElementById("addressForm")
  const formTitle = document.getElementById("formTitle")

  // Variabel untuk tracking mode edit
  let isEditMode = false
  let editingAddressId = null

  // Event listeners
  addAddressBtn.addEventListener("click", openAddForm)
  closeFormBtn.addEventListener("click", closeForm)
  cancelBtn.addEventListener("click", closeForm)
  addressForm.addEventListener("submit", handleFormSubmit)

  // Event listener untuk tombol edit (menggunakan event delegation)
  document.addEventListener("click", (e) => {
    if (e.target.closest(".edit-address-btn")) {
      const addressId = Number.parseInt(e.target.closest(".edit-address-btn").dataset.id)
      openEditForm(addressId)
    }

    if (e.target.closest(".delete-address-btn")) {
      const addressId = Number.parseInt(e.target.closest(".delete-address-btn").dataset.id)
      deleteAddress(addressId)
    }

    if (e.target.closest(".set-primary-btn")) {
      const addressId = Number.parseInt(e.target.closest(".set-primary-btn").dataset.id)
      setPrimaryAddress(addressId)
    }
  })

  // Fungsi untuk membuka form tambah alamat
  function openAddForm() {
    isEditMode = false
    editingAddressId = null
    formTitle.textContent = "Tambah Alamat Baru"
    clearForm()
    showForm()
  }

  // Fungsi untuk membuka form edit alamat
  function openEditForm(addressId) {
    const address = addressData.find((addr) => addr.id === addressId)
    if (!address) {
      showToast("Alamat tidak ditemukan!", "error")
      return
    }

    isEditMode = true
    editingAddressId = addressId
    formTitle.textContent = "Edit Alamat"

    // Isi form dengan data alamat yang akan diedit
    fillForm(address)
    showForm()
  }

  // Fungsi untuk mengisi form dengan data alamat
  function fillForm(address) {
    document.getElementById("addressLabel").value = address.label
    document.getElementById("recipientName").value = address.name
    document.getElementById("phoneNumber").value = address.phone
    document.getElementById("province").value = address.province
    document.getElementById("city").value = address.city
    document.getElementById("district").value = address.district
    document.getElementById("postalCode").value = address.postalCode
    document.getElementById("fullAddress").value = address.fullAddress
    document.getElementById("setAsPrimary").checked = address.isPrimary
  }

  // Fungsi untuk mengosongkan form
  function clearForm() {
    addressForm.reset()
    document.getElementById("setAsPrimary").checked = false
  }

  // Fungsi untuk menampilkan form
  function showForm() {
    addressFormContainer.style.display = "flex"
    document.body.style.overflow = "hidden" // Prevent scrolling
  }

  // Fungsi untuk menutup form
  function closeForm() {
    addressFormContainer.style.display = "none"
    document.body.style.overflow = "auto" // Restore scrolling
    clearForm()
    isEditMode = false
    editingAddressId = null
  }

  // Fungsi untuk menangani submit form
  function handleFormSubmit(e) {
    e.preventDefault()

    const formData = {
      label: document.getElementById("addressLabel").value,
      name: document.getElementById("recipientName").value,
      phone: document.getElementById("phoneNumber").value,
      province: document.getElementById("province").value,
      city: document.getElementById("city").value,
      district: document.getElementById("district").value,
      postalCode: document.getElementById("postalCode").value,
      fullAddress: document.getElementById("fullAddress").value,
      isPrimary: document.getElementById("setAsPrimary").checked,
    }

    if (isEditMode) {
      updateAddress(editingAddressId, formData)
    } else {
      addNewAddress(formData)
    }
  }

  // Fungsi untuk menambah alamat baru
  function addNewAddress(formData) {
    const newId = Math.max(...addressData.map((addr) => addr.id)) + 1
    const iconMap = {
      Rumah: "fas fa-home",
      Kantor: "fas fa-building",
      Sekolah: "fas fa-school",
      Toko: "fas fa-store",
      default: "fas fa-map-marker-alt",
    }

    const newAddress = {
      id: newId,
      label: formData.label,
      icon: iconMap[formData.label] || iconMap.default,
      name: formData.name,
      phone: formData.phone,
      province: formData.province,
      city: formData.city,
      district: formData.district,
      postalCode: formData.postalCode,
      fullAddress: formData.fullAddress,
      isPrimary: formData.isPrimary,
    }

    // Jika alamat baru dijadikan utama, ubah alamat lain menjadi tidak utama
    if (formData.isPrimary) {
      addressData.forEach((addr) => (addr.isPrimary = false))
    }

    addressData.push(newAddress)
    renderAddresses()
    closeForm()
    showToast("Alamat berhasil ditambahkan!", "success")
  }

  // Fungsi untuk mengupdate alamat
  function updateAddress(addressId, formData) {
    const addressIndex = addressData.findIndex((addr) => addr.id === addressId)
    if (addressIndex === -1) {
      showToast("Alamat tidak ditemukan!", "error")
      return
    }

    const iconMap = {
      Rumah: "fas fa-home",
      Kantor: "fas fa-building",
      Sekolah: "fas fa-school",
      Toko: "fas fa-store",
      default: "fas fa-map-marker-alt",
    }

    // Jika alamat dijadikan utama, ubah alamat lain menjadi tidak utama
    if (formData.isPrimary) {
      addressData.forEach((addr) => (addr.isPrimary = false))
    }

    // Update data alamat
    addressData[addressIndex] = {
      ...addressData[addressIndex],
      label: formData.label,
      icon: iconMap[formData.label] || iconMap.default,
      name: formData.name,
      phone: formData.phone,
      province: formData.province,
      city: formData.city,
      district: formData.district,
      postalCode: formData.postalCode,
      fullAddress: formData.fullAddress,
      isPrimary: formData.isPrimary,
    }

    renderAddresses()
    closeForm()
    showToast("Alamat berhasil diperbarui!", "success")
  }

  // Fungsi untuk menghapus alamat
  function deleteAddress(addressId) {
    if (confirm("Apakah Anda yakin ingin menghapus alamat ini?")) {
      const addressIndex = addressData.findIndex((addr) => addr.id === addressId)
      if (addressIndex !== -1) {
        const deletedAddress = addressData[addressIndex]
        addressData.splice(addressIndex, 1)

        // Jika alamat yang dihapus adalah alamat utama, jadikan alamat pertama sebagai utama
        if (deletedAddress.isPrimary && addressData.length > 0) {
          addressData[0].isPrimary = true
        }

        renderAddresses()
        showToast("Alamat berhasil dihapus!", "success")
      }
    }
  }

  // Fungsi untuk menjadikan alamat sebagai alamat utama
  function setPrimaryAddress(addressId) {
    // Set semua alamat menjadi tidak utama
    addressData.forEach((addr) => (addr.isPrimary = false))

    // Set alamat yang dipilih menjadi utama
    const address = addressData.find((addr) => addr.id === addressId)
    if (address) {
      address.isPrimary = true
      renderAddresses()
      showToast("Alamat utama berhasil diubah!", "success")
    }
  }

  // Fungsi untuk render ulang daftar alamat
  function renderAddresses() {
    const addressList = document.querySelector(".address-list")
    addressList.innerHTML = ""

    addressData.forEach((address) => {
      const addressCard = createAddressCard(address)
      addressList.appendChild(addressCard)
    })
  }

  // Fungsi untuk membuat elemen kartu alamat
  function createAddressCard(address) {
    const card = document.createElement("div")
    card.className = "address-card"

    const cityNames = {
      purwokerto: "Purwokerto",
      purbalingga: "Purbalingga",
      banyumas: "Banyumas",
      cilacap: "Cilacap",
    }

    const districtNames = {
      "maju-jaya": "Maju Jaya",
      sukses: "Sukses",
      harmoni: "Harmoni",
      sejahtera: "Sejahtera",
    }

    card.innerHTML = `
            ${address.isPrimary ? '<div class="address-badge">Utama</div>' : ""}
            <div class="address-content">
                <h3><i class="${address.icon}"></i> ${address.label}</h3>
                <p class="address-name">${address.name}</p>
                <p class="address-phone"><i class="fas fa-phone"></i> ${address.phone}</p>
                <p class="address-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    ${address.fullAddress}, Kecamatan ${districtNames[address.district] || address.district}, ${cityNames[address.city] || address.city}, ${address.postalCode}
                </p>
            </div>
            <div class="address-actions">
                <button class="btn btn-outline btn-sm edit-address-btn" data-id="${address.id}">
                    <i class="fas fa-edit"></i> Ubah
                </button>
                <button class="btn btn-outline btn-sm btn-danger delete-address-btn" data-id="${address.id}">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                ${
                  !address.isPrimary
                    ? `
                    <button class="btn set-primary-btn" data-id="${address.id}">
                        <i class="fas fa-check-circle"></i> Jadikan Alamat Utama
                    </button>
                `
                    : ""
                }
            </div>
        `

    return card
  }

  // Fungsi untuk menampilkan toast notification
  function showToast(message, type = "success") {
    // Hapus toast yang ada
    const existingToast = document.querySelector(".toast")
    if (existingToast) {
      existingToast.remove()
    }

    const toast = document.createElement("div")
    toast.className = `toast toast-${type}`
    toast.innerHTML = `
            <div class="toast-content">
                <i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close">
                <i class="fas fa-times"></i>
            </button>
        `

    document.body.appendChild(toast)

    // Tampilkan toast
    setTimeout(() => toast.classList.add("show"), 100)

    // Hapus toast setelah 3 detik
    setTimeout(() => {
      toast.classList.remove("show")
      setTimeout(() => toast.remove(), 300)
    }, 3000)

    // Event listener untuk tombol close
    toast.querySelector(".toast-close").addEventListener("click", () => {
      toast.classList.remove("show")
      setTimeout(() => toast.remove(), 300)
    })
  }

  // Event listener untuk menutup modal ketika klik di luar
  addressFormContainer.addEventListener("click", (e) => {
    if (e.target === addressFormContainer) {
      closeForm()
    }
  })

  // Event listener untuk ESC key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && addressFormContainer.style.display === "flex") {
      closeForm()
    }
  })

  // Inisialisasi: render alamat yang ada
  renderAddresses()
})
