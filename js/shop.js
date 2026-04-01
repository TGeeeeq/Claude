// Cart management with improved error handling
let cart = JSON.parse(localStorage.getItem("cart")) || []

// API Client instance
const api = new ApiClient()

// Load categories and products on page load
document.addEventListener("DOMContentLoaded", () => {
  loadCategories().then(() => {
    const lastCategory = sessionStorage.getItem('lastCategory') || ""
    if (lastCategory) {
      filterCategory(lastCategory)
    } else {
      loadProducts()
    }
  })
  updateCartUI()
})

// Load categories for filter buttons
async function loadCategories() {
  const container = document.getElementById("categoryFilters")
  if (!container) return

  try {
    const data = await api.get('../api/categories.php')

    if (data.success) {
      // Clear and rebuild
      const allBtn = container.querySelector('[data-category=""]')
      container.innerHTML = ''
      if (allBtn) {
        allBtn.classList.add('active')
        container.appendChild(allBtn)
      }

      data.categories.forEach((category) => {
        if (category.product_count > 0) {
          const btn = document.createElement("button")
          btn.className = "filter-btn"
          btn.setAttribute("data-category", category.slug)
          btn.setAttribute("aria-label", `Filtrovat podle ${category.name}`)
          btn.textContent = `${category.name}`
          btn.onclick = () => filterCategory(category.slug)
          container.appendChild(btn)
        }
      })
    }
  } catch (error) {
    console.error("Error loading categories:", error)
    showErrorMessage(container, "Nepodařilo se načíst kategorie")
  }
}

// Load and display products with loading states
async function loadProducts(category = "", search = "") {
  const grid = document.getElementById("productsGrid")
  const loading = document.getElementById("loadingSpinner")
  const noProducts = document.getElementById("noProducts")

  if (!grid || !loading || !noProducts) return

  // Show loading state
  loading.style.display = "block"
  grid.style.display = "none"
  noProducts.style.display = "none"

  // Remove any existing error messages
  const existingError = grid.parentElement.querySelector('.error-message')
  if (existingError) existingError.remove()

  try {
    const params = {}
    if (category) params.category = category
    if (search) params.search = search

    const data = await api.get('../api/products.php', params)

    loading.style.display = "none"

    if (data.success && data.products && data.products.length > 0) {
      grid.innerHTML = data.products
        .map((product) => `
          <div class="product-card" onclick="openProductDetail('${product.slug}')" style="cursor: pointer;" role="button" tabindex="0" aria-label="Zobrazit ${product.name}">
            <img
              src="${product.image_url || '../assets/logo.png'}"
              alt="${product.name}"
              class="product-image"
              loading="lazy"
            >
            <div class="product-info">
              ${product.category_name ? `<div class="product-category">${product.category_name}</div>` : ''}
              <h3 class="product-name">${product.name}</h3>
              <p class="product-description">${product.description || ''}</p>
              <div class="product-footer">
                <div class="product-price">${formatPrice(product.price)} Kč</div>
                <button
                  class="add-to-cart-btn"
                  onclick="event.stopPropagation(); addToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${product.image_url || '../assets/logo.png'}')"
                  aria-label="Přidat ${product.name} do košíku"
                >
                  <i class="fas fa-cart-plus" aria-hidden="true"></i> Do košíku
                </button>
              </div>
            </div>
          </div>
        `).join("")
      grid.style.display = "grid"
    } else {
      noProducts.style.display = "block"
    }
  } catch (error) {
    console.error("Error loading products:", error)
    loading.style.display = "none"

    // Show error message
    showErrorMessage(grid.parentElement, "Nepodařilo se načíst produkty. Zkuste to znovu později.")
  }
}

// Show error message
function showErrorMessage(container, message) {
  const errorEl = document.createElement('div')
  errorEl.className = 'error-message'
  errorEl.setAttribute('role', 'alert')
  errorEl.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`
  container.appendChild(errorEl)
}

// Filter products by category
function filterCategory(categorySlug) {
  sessionStorage.setItem('lastCategory', categorySlug)

  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active")
    if (btn.getAttribute("data-category") === categorySlug) {
      btn.classList.add("active")
    }
  })

  const searchInput = document.getElementById("searchInput")
  const searchValue = searchInput ? searchInput.value : ""
  loadProducts(categorySlug, searchValue)
}

// Handle search with debounce
let searchTimeout
function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    const searchValue = document.getElementById("searchInput")?.value || ""
    const activeCategory = document.querySelector(".filter-btn.active")?.getAttribute("data-category") || ""
    loadProducts(activeCategory, searchValue)
  }, 300)
}

// Scroll to products section
function scrollToProducts() {
  const productsSection = document.getElementById("products")
  if (productsSection) {
    productsSection.scrollIntoView({ behavior: "smooth" })
  }
}

// Cart functions
function addToCart(id, name, price, image) {
  const existingItem = cart.find((item) => item.id === id)

  if (existingItem) {
    existingItem.quantity++
  } else {
    cart.push({
      id,
      name,
      price,
      image,
      quantity: 1,
    })
  }

  saveCart()
  updateCartUI()
  showCartNotification()
}

function removeFromCart(id) {
  cart = cart.filter((item) => item.id !== id)
  saveCart()
  updateCartUI()
}

function updateQuantity(id, change) {
  const item = cart.find((item) => item.id === id)
  if (item) {
    item.quantity += change
    if (item.quantity <= 0) {
      removeFromCart(id)
    } else {
      saveCart()
      updateCartUI()
    }
  }
}

function saveCart() {
  try {
    localStorage.setItem("cart", JSON.stringify(cart))
  } catch (e) {
    console.error("Error saving cart:", e)
  }
}

function updateCartUI() {
  const cartCount = document.getElementById("cartCount")
  const cartContent = document.getElementById("cartContent")
  const cartTotal = document.getElementById("cartTotal")

  if (!cartCount || !cartContent || !cartTotal) return

  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0)
  const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0)

  cartCount.textContent = totalItems
  cartCount.setAttribute('aria-label', `Košík obsahuje ${totalItems} položek`)
  cartTotal.textContent = formatPrice(totalPrice) + " Kč"

  if (cart.length === 0) {
    cartContent.innerHTML = '<div class="empty-cart"><p>Váš košík je prázdný</p></div>'
  } else {
    cartContent.innerHTML = cart
      .map((item) => `
        <div class="cart-item">
          <img src="${item.image || '../assets/logo.png'}" alt="${item.name}" class="cart-item-image">
          <div class="cart-item-details">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-price">${formatPrice(item.price)} Kč</div>
            <div class="cart-item-controls">
              <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)" aria-label="Snížit množství">−</button>
              <span aria-label="Počet kusů">${item.quantity}</span>
              <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)" aria-label="Zvýšit množství">+</button>
              <button class="remove-btn" onclick="removeFromCart(${item.id})" aria-label="Odebrat z košíku">Odebrat</button>
            </div>
          </div>
        </div>
      `).join("")
  }
}

function toggleCart() {
  const sidebar = document.getElementById("cartSidebar")
  const overlay = document.getElementById("cartOverlay")

  if (sidebar && overlay) {
    sidebar.classList.toggle("active")
    overlay.classList.toggle("active")
  }
}

function showCartNotification() {
  toggleCart()
}

function goToCheckout() {
  if (cart.length === 0) {
    alert("Váš košík je prázdný")
    return
  }
  window.location.href = "checkout"
}

function openProductDetail(slug) {
  window.location.href = `product-detail?product=${encodeURIComponent(slug)}`
}

function formatPrice(price) {
  return new Intl.NumberFormat("cs-CZ").format(price)
}