let cart = JSON.parse(localStorage.getItem("cart")) || []

// Load cart summary on page load
document.addEventListener("DOMContentLoaded", () => {
  // Redirect if cart is empty
  if (cart.length === 0) {
    window.location.href = "index"
    return
  }

  loadOrderSummary()
})

// Load order summary
function loadOrderSummary() {
  const summaryItems = document.getElementById("summaryItems")
  const subtotal = document.getElementById("subtotal")
  const total = document.getElementById("total")

  if (!summaryItems || !subtotal || !total) return;

  const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0)

  summaryItems.innerHTML = cart
    .map(
      (item) => `
        <div class="summary-item" style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center;">
            <img src="${item.image || "../assets/logo.png"}" alt="${item.name}" class="summary-item-image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
            <div class="summary-item-details" style="flex: 1;">
                <div class="summary-item-name" style="font-weight: 600;">${item.name}</div>
                <div class="summary-item-qty" style="font-size: 0.9rem; color: var(--text-color-light);">Množství: ${item.quantity}×</div>
            </div>
            <div class="summary-item-price" style="font-weight: 600;">${formatPrice(item.price * item.quantity)} Kč</div>
        </div>
    `,
    )
    .join("")

  subtotal.textContent = formatPrice(totalPrice) + " Kč"
  total.textContent = formatPrice(totalPrice) + " Kč"
}

// Submit order
async function submitOrder(event) {
  event.preventDefault()

  const submitBtn = document.getElementById("submitBtn")
  submitBtn.disabled = true
  submitBtn.textContent = "Odesílám..."

  const formData = new FormData(event.target)

  // Build address string
  const address = `${formData.get("street")}, ${formData.get("postal_code")} ${formData.get("city")}`

  // Prepare order data
  const orderData = {
    customer_name: formData.get("customer_name"),
    customer_email: formData.get("customer_email"),
    customer_phone: formData.get("customer_phone") || "",
    shipping_address: address,
    payment_method: formData.get("payment_method"),
    notes: formData.get("notes") || "",
    items: cart.map((item) => ({
      product_id: item.id,
      product_name: item.name,
      quantity: item.quantity,
      unit_price: item.price,
      total_price: item.price * item.quantity,
    })),
    total_amount: cart.reduce((sum, item) => sum + item.price * item.quantity, 0),
  }

  try {
    const response = await fetch("../api/create-order.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(orderData),
    })

    const result = await response.json()

    if (result.success) {
      // Clear cart
      localStorage.removeItem("cart")
      cart = []

      // Show success modal
      alert("Děkujeme za objednávku! Číslo vaší objednávky je: " + result.order_number)
      window.location.href = "index"
    } else {
      alert("Chyba při vytváření objednávky: " + (result.error || "Neznámá chyba"))
      submitBtn.disabled = false
      submitBtn.textContent = "Odeslat objednávku"
    }
  } catch (error) {
    console.error("Error submitting order:", error)
    alert("Nepodařilo se odeslat objednávku. Zkuste to prosím znovu.")
    submitBtn.disabled = false
    submitBtn.textContent = "Odeslat objednávku"
  }
}

// Helper function to format price
function formatPrice(price) {
  return new Intl.NumberFormat("cs-CZ").format(price)
}
