// Cart management
let cart = JSON.parse(localStorage.getItem("cart")) || []

// Load categories and products on page load
document.addEventListener("DOMContentLoaded", () => {
  // Nejprve načteme kategorie a až po jejich načtení aplikujeme filtr
  loadCategories().then(() => {
    const lastCategory = sessionStorage.getItem('lastCategory') || "";
    if (lastCategory) {
      filterCategory(lastCategory);
    } else {
      loadProducts();
    }
  });
  updateCartUI();
})

// Load categories for filter buttons
async function loadCategories() {
  try {
    const response = await fetch("../api/categories.php")
    const data = await response.json()

    if (data.success) {
      const filtersContainer = document.getElementById("categoryFilters")
      if (!filtersContainer) return;

      // Clear existing except "All"
      const allBtn = filtersContainer.querySelector('[data-category=""]')
      filtersContainer.innerHTML = ''
      if (allBtn) filtersContainer.appendChild(allBtn)

      data.categories.forEach((category) => {
        if (category.product_count > 0) {
          const btn = document.createElement("button")
          btn.className = "filter-btn"
          btn.setAttribute("data-category", category.slug)
          btn.textContent = `${category.name}`
          btn.onclick = () => filterCategory(category.slug)
          filtersContainer.appendChild(btn)
        }
      })
    }
  } catch (error) {
    console.error("Error loading categories:", error)
  }
}

// Load and display products
async function loadProducts(category = "", search = "") {
  const grid = document.getElementById("productsGrid")
  const loading = document.getElementById("loadingSpinner")
  const noProducts = document.getElementById("noProducts")

  if (!grid || !loading || !noProducts) return;

  loading.style.display = "block"
  grid.style.display = "none"
  noProducts.style.display = "none"

  try {
    let url = "../api/products.php?"
    if (category) url += `category=${category}&`
    if (search) url += `search=${encodeURIComponent(search)}`

    const response = await fetch(url)
    const data = await response.json()

    loading.style.display = "none"

    if (data.success && data.products.length > 0) {
      grid.innerHTML = data.products
        .map(
          (product) => `
                <div class="product-card" onclick="openProductDetail('${product.slug}')" style="cursor: pointer;">
                    <img 
                        src="${product.image_url || "../assets/logo.png"}" 
                        alt="${product.name}"
                        class="product-image"
                    >
                    <div class="product-info">
                        ${product.category_name ? `<div class="product-category">${product.category_name}</div>` : ""}
                        <h3 class="product-name">${product.name}</h3>
                        <p class="product-description">${product.description || ""}</p>
                        <div class="product-footer">
                            <div class="product-price">${formatPrice(product.price)} Kč</div>
                            <button 
                                class="add-to-cart-btn" 
                                onclick="event.stopPropagation(); addToCart(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${product.image_url || "../assets/logo.png"}')"
                            >
                                <i class="fas fa-cart-plus"></i> Do košíku
                            </button>
                        </div>
                    </div>
                </div>
            `,
        )
        .join("")
      grid.style.display = "grid"
    } else {
      noProducts.style.display = "block"
    }
  } catch (error) {
    console.error("Error loading products:", error)
    loading.style.display = "none"
    noProducts.style.display = "block"
  }
}

// Filter products by category
function filterCategory(categorySlug) {
  // Uložení vybrané kategorie do paměti prohlížeče
  sessionStorage.setItem('lastCategory', categorySlug);

  // Update active button
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active")
    if (btn.getAttribute("data-category") === categorySlug) {
      btn.classList.add("active")
    }
  })

  // Load filtered products
  const searchInput = document.getElementById("searchInput");
  const searchValue = searchInput ? searchInput.value : "";
  loadProducts(categorySlug, searchValue)
}

// Handle search
let searchTimeout
function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    const searchValue = document.getElementById("searchInput").value
    const activeCategory = document.querySelector(".filter-btn.active")?.getAttribute("data-category") || ""
    loadProducts(activeCategory, searchValue)
  }, 300)
}

// Scroll to products section
function scrollToProducts() {
  const productsSection = document.getElementById("products");
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
  localStorage.setItem("cart", JSON.stringify(cart))
}

function updateCartUI() {
  const cartCount = document.getElementById("cartCount")
  const cartContent = document.getElementById("cartContent")
  const cartTotal = document.getElementById("cartTotal")

  if (!cartCount || !cartContent || !cartTotal) return;

  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0)
  const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0)

  cartCount.textContent = totalItems
  cartTotal.textContent = formatPrice(totalPrice) + " Kč"

  if (cart.length === 0) {
    cartContent.innerHTML = '<div class="empty-cart"><p>Váš košík je prázdný</p></div>'
  } else {
    cartContent.innerHTML = cart
      .map(
        (item) => `
            <div class="cart-item">
                <img src="${item.image || "../assets/logo.png"}" alt="${item.name}" class="cart-item-image">
                <div class="cart-item-details">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatPrice(item.price)} Kč</div>
                    <div class="cart-item-controls">
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">−</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        <button class="remove-btn" onclick="removeFromCart(${item.id})">Odebrat</button>
                    </div>
                </div>
            </div>
        `,
      )
      .join("")
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
  toggleCart() // Open cart after adding item
}

function goToCheckout() {
  window.location.href = "checkout.html"
}

// Open product detail page
function openProductDetail(slug) {
  window.location.href = `product-detail.html?product=${encodeURIComponent(slug)}`
}

// Helper function to format price
function formatPrice(price) {
  return new Intl.NumberFormat("cs-CZ").format(price)
}
