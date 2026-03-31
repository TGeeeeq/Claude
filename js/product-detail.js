// Product Detail Page Script

let currentProduct = null;
let currentImageIndex = 0;
let productImages = [];

// Load product on page load
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const productSlug = params.get("product");

  if (!productSlug) {
    showError("Produkt nebyl vybrán");
    return;
  }

  loadProductDetail(productSlug);
  updateCartUI();
});

// Load product detail with images
async function loadProductDetail(slug) {
  const container = document.getElementById("productDetailContainer");

  try {
    const response = await fetch(`../api/product-detail-with-images.php?slug=${encodeURIComponent(slug)}`);
    const data = await response.json();

    if (!data.success) {
      showError("Produkt nebyl nalezen");
      return;
    }

    currentProduct = data.product;
    productImages = data.images || [];
    currentImageIndex = 0;

    renderProductDetail(container);
  } catch (error) {
    console.error("Error loading product:", error);
    showError("Chyba při načítání produktu");
  }
}

// Render product detail
function renderProductDetail(container) {
  const product = currentProduct;
  const stockStatus = getStockStatus(product.stock_quantity);

  let imagesHTML = "";
  if (productImages.length > 0) {
    imagesHTML = `
      <div class="product-gallery">
        <div class="main-image-container">
          <img 
            src="${productImages[currentImageIndex].image_url || '../assets/logo.png'}" 
            alt="${product.name}"
            class="main-image"
            id="mainImage"
          >
          ${productImages.length > 1 ? `<div class="image-counter">${currentImageIndex + 1} / ${productImages.length}</div>` : ''}
        </div>
        ${productImages.length > 1 ? `
          <div class="thumbnail-gallery" id="thumbnailGallery">
            ${productImages.map((img, index) => `
              <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="selectImage(${index})">
                <img src="${img.image_url || '../assets/logo.png'}" alt="Fotografie ${index + 1}">
              </div>
            `).join('')}
          </div>
        ` : ''}
      </div>
    `;
  }

  const detailsHTML = `
    <div class="product-detail-content">
      ${imagesHTML}
      
      <div class="product-info-section">
        <div class="product-detail-header">
          ${product.category_name ? `<div class="product-detail-category">${product.category_name}</div>` : ''}
          <h1 class="product-detail-name">${product.name}</h1>
          <div class="product-detail-price">${formatPrice(product.price)} Kč</div>
        </div>

        ${product.description ? `
          <div class="product-detail-description">
            ${product.description}
          </div>
        ` : ''}

        <div class="stock-status ${stockStatus.class}">
          ${stockStatus.text}
        </div>

        <div class="product-details-grid">
          <div class="detail-item">
            <div class="detail-label">Kategorie</div>
            <div class="detail-value">${product.category_name || 'Bez kategorie'}</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Skladem</div>
            <div class="detail-value">${product.stock_quantity} ks</div>
          </div>
        </div>

        <div class="product-actions">
          <div class="quantity-selector">
            <button class="qty-btn-detail" onclick="changeQuantity(-1)">−</button>
            <input type="number" id="quantityInput" class="quantity-input" value="1" min="1" max="${product.stock_quantity}">
            <button class="qty-btn-detail" onclick="changeQuantity(1)">+</button>
          </div>
          <button 
            class="add-to-cart-btn-detail" 
            onclick="addToCartDetail(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, '${(productImages[0]?.image_url || product.image_url || '../assets/logo.png').replace(/'/g, "\\'")}')"
            ${product.stock_quantity <= 0 ? 'disabled' : ''}
          >
            <i class="fas fa-cart-plus"></i> Přidat do košíku
          </button>
        </div>
      </div>
    </div>
  `;

  container.innerHTML = detailsHTML;
}

// Select image from gallery
function selectImage(index) {
  if (index >= 0 && index < productImages.length) {
    currentImageIndex = index;
    const mainImage = document.getElementById("mainImage");
    const thumbnails = document.querySelectorAll(".thumbnail");

    mainImage.src = productImages[index].image_url || "../assets/logo.png";

    thumbnails.forEach((thumb, i) => {
      thumb.classList.toggle("active", i === index);
    });

    // Update counter if exists
    const counter = document.querySelector(".image-counter");
    if (counter) {
      counter.textContent = `${index + 1} / ${productImages.length}`;
    }
  }
}

// Change quantity
function changeQuantity(change) {
  const input = document.getElementById("quantityInput");
  let newValue = parseInt(input.value) + change;

  const maxStock = currentProduct.stock_quantity;
  if (newValue < 1) newValue = 1;
  if (newValue > maxStock) newValue = maxStock;

  input.value = newValue;
}

// Add to cart from detail page
function addToCartDetail(id, name, price, image) {
  const quantity = parseInt(document.getElementById("quantityInput").value) || 1;

  for (let i = 0; i < quantity; i++) {
    addToCart(id, name, price, image);
  }

  // Reset quantity
  document.getElementById("quantityInput").value = 1;

  // Show notification
  showAddToCartNotification();
}

// Show notification
function showAddToCartNotification() {
  const notification = document.createElement("div");
  notification.className = "add-to-cart-notification";
  notification.innerHTML = `
    <i class="fas fa-check-circle"></i>
    <span>Přidáno do košíku</span>
  `;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.classList.add("show");
  }, 100);

  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => notification.remove(), 300);
  }, 2000);
}

// Get stock status
function getStockStatus(quantity) {
  if (quantity <= 0) {
    return {
      class: "out-of-stock",
      text: "Vyprodáno"
    };
  } else if (quantity <= 5) {
    return {
      class: "low-stock",
      text: `Poslední kusy (${quantity} ks)`
    };
  } else {
    return {
      class: "in-stock",
      text: "Skladem"
    };
  }
}

// Show error
function showError(message) {
  const container = document.getElementById("productDetailContainer");
  container.innerHTML = `
    <div class="error-message">
      <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
      <p>${message}</p>
      <a href="index.html">Zpět na obchod</a>
    </div>
  `;
}

// Helper function to format price
function formatPrice(price) {
  return new Intl.NumberFormat("cs-CZ").format(price);
}
