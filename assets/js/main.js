/**
 * ============================================================
 *    Dhoti Mahal — Main JavaScript
 *    ============================================================
 *
 * @format
 */

document.addEventListener("DOMContentLoaded", function () {
  // ---- Mobile Nav Toggle ----
  const navToggle = document.getElementById("navToggle");
  const navList = document.getElementById("navList");
  if (navToggle && navList) {
    navToggle.addEventListener("click", () => {
      navList.classList.toggle("open");
    });
  }

  // ---- Hero Slider ----
  const slides = document.querySelectorAll(".hero-slide");
  const dots = document.querySelectorAll(".hero-dot");
  let current = 0;
  let timer;

  function showSlide(n) {
    slides.forEach((s) => s.classList.remove("active"));
    dots.forEach((d) => d.classList.remove("active"));
    slides[n].classList.add("active");
    if (dots[n]) dots[n].classList.add("active");
    current = n;
  }

  function nextSlide() {
    showSlide((current + 1) % slides.length);
  }

  if (slides.length > 1) {
    timer = setInterval(nextSlide, 4000);
    dots.forEach((dot, i) => {
      dot.addEventListener("click", () => {
        clearInterval(timer);
        showSlide(i);
        timer = setInterval(nextSlide, 4000);
      });
    });
  }

  // ---- Product Gallery Thumbnails ----
  const thumbs = document.querySelectorAll(".thumb");
  const mainImg = document.getElementById("mainProductImg");
  thumbs.forEach((thumb) => {
    thumb.addEventListener("click", () => {
      thumbs.forEach((t) => t.classList.remove("active"));
      thumb.classList.add("active");
      if (mainImg) mainImg.src = thumb.dataset.full;
    });
  });

  // ---- Size Selector ----
  document.querySelectorAll(".size-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".size-btn")
        .forEach((b) => b.classList.remove("selected"));
      btn.classList.add("selected");
      const sizeInput = document.getElementById("selectedSize");
      if (sizeInput) sizeInput.value = btn.dataset.size;
    });
  });

  // ---- Quantity Controls ----
  document.querySelectorAll(".qty-ctrl").forEach((ctrl) => {
    const input = ctrl.querySelector("input");
    ctrl.querySelector(".qty-minus")?.addEventListener("click", () => {
      const val = parseInt(input.value) || 1;
      if (val > 1) input.value = val - 1;
    });
    ctrl.querySelector(".qty-plus")?.addEventListener("click", () => {
      const val = parseInt(input.value) || 1;
      const max = parseInt(input.max) || 99;
      if (val < max) input.value = val + 1;
    });
  });

  // ---- Buy Now Form: Populate hidden fields from selections ----
  const buyNowForm = document.querySelector('form[action*="checkout.php"]');
  if (buyNowForm) {
    buyNowForm.addEventListener("submit", function (e) {
      const selectedSize = document.getElementById("selectedSize");
      const quantityInput = document.getElementById("quantityInput");
      const buyNowSizeField = document.getElementById("selectedSize1");
      const buyNowQtyField = document.getElementById("showQty1");

      // Populate hidden fields from user selections
      if (buyNowSizeField && selectedSize) {
        buyNowSizeField.value = selectedSize.value || "";
      }
      if (buyNowQtyField && quantityInput) {
        buyNowQtyField.value = parseInt(quantityInput.value) || 1;
      }
    });
  }

  // ---- Product Tabs ----
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const target = btn.dataset.tab;
      document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
      document
        .querySelectorAll(".tab-content")
        .forEach((c) => c.classList.remove("active"));
      btn.classList.add("active");
      const content = document.getElementById(target);
      if (content) content.classList.add("active");
    });
  });

  // ---- Cart AJAX: Add to Cart ----
  document.querySelectorAll(".add-to-cart-form").forEach((form) => {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const btn = form.querySelector('button[type="submit"]');
      const orig = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

      try {
        const data = new FormData(form);

        const res = await fetch("/dhoti-mahal/api/cart.php", {
          method: "POST",
          body: data,
        });

        const text = await res.text();

        console.log("STATUS:", res.status);
        console.log("RESPONSE TEXT:", text);

        let json;
        try {
          json = JSON.parse(text);
        } catch (e) {
          console.error("JSON ERROR:", e);
          showToast("Invalid server response", "error");
          return;
        }

        if (json.success) {
          showToast("Added successfully", "success");
        } else {
          showToast(json.message || "Failed", "error");
        }
      } catch (err) {
        console.error("FETCH ERROR:", err);
        showToast("Something went wrong. Please try again.", "error");
      }
      setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
        btn.style.background = "";
      }, 2000);
    });
  });

  // ---- Cart Page: Update / Remove ----
  document.querySelectorAll(".cart-qty-input").forEach((input) => {
    input.addEventListener("change", async function () {
      const key = this.dataset.key;
      const qty = parseInt(this.value) || 1;
      await cartAction({ action: "update", key, qty });
    });
  });

  document.querySelectorAll(".cart-remove-btn").forEach((btn) => {
    btn.addEventListener("click", async function () {
      const key = this.dataset.key;
      if (confirm("Remove this item from cart?")) {
        await cartAction({ action: "remove", key });
        this.closest(".cart-item").remove();
        updateCartTotals();
      }
    });
  });

  async function cartAction(params) {
    try {
      const form = new FormData();
      Object.entries(params).forEach(([k, v]) => form.append(k, v));
      const res = await fetch(BASE_URL + "api/cart.php", {
        method: "POST",
        body: form,
      });
      const json = await res.json();
      if (json.cart_count !== undefined) {
        const badge = document.querySelector(".cart-badge");
        if (badge) badge.textContent = json.cart_count;
      }
      if (json.subtotal !== undefined) {
        document
          .querySelectorAll(".cart-subtotal")
          .forEach((el) => (el.textContent = json.subtotal));
        document
          .querySelectorAll(".cart-total")
          .forEach((el) => (el.textContent = json.total));
      }
      return json;
    } catch (e) {
      console.error("Cart error", e);
    }
  }

  function updateCartTotals() {
    const items = document.querySelectorAll(".cart-item");
    if (items.length === 0) {
      document.querySelector(".cart-layout")?.remove();
      const main = document.querySelector("main");
      if (main) {
        main.innerHTML += `
                    <div class="container">
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>Your cart is empty</h3>
                            <p>Browse our collection and add items to your cart</p>
                            <a href="${BASE_URL}pages/category.php" class="btn btn-primary">Shop Now</a>
                        </div>
                    </div>`;
      }
    }
  }

  // ---- UPI Copy Button ----
  const copyUpiBtn = document.getElementById("copyUpiBtn");
  if (copyUpiBtn) {
    copyUpiBtn.addEventListener("click", function () {
      const upiId = this.dataset.upi;
      navigator.clipboard.writeText(upiId).then(() => {
        this.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(
          () => (this.innerHTML = '<i class="fas fa-copy"></i> Copy UPI ID'),
          2000,
        );
      });
    });
  }

  // ---- Flash Auto-Dismiss ----
  const flash = document.getElementById("flashMsg");
  if (flash) {
    setTimeout(() => (flash.style.opacity = "0"), 4000);
    setTimeout(() => flash.remove(), 4500);
    flash.style.transition = "opacity 0.5s ease";
  }

  // ---- Toast Notification ----
  function showToast(msg, type = "info") {
    const toast = document.createElement("div");
    toast.className = `flash-message flash-${type}`;
    toast.style.cssText =
      "position:fixed;top:20px;right:20px;z-index:9999;border-radius:6px;padding:14px 20px;box-shadow:0 4px 16px rgba(0,0,0,0.2);";
    toast.innerHTML = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  // ---- Admin: Confirm Delete ----
  document.querySelectorAll(".confirm-delete").forEach((link) => {
    link.addEventListener("click", function (e) {
      if (
        !confirm(
          "Are you sure you want to delete this? This action cannot be undone.",
        )
      ) {
        e.preventDefault();
      }
    });
  });

  // ---- Admin: Image Preview ----
  const imageInput = document.getElementById("productImage");
  const imagePreview = document.getElementById("imagePreview");
  if (imageInput && imagePreview) {
    imageInput.addEventListener("change", function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
          imagePreview.src = e.target.result;
          imagePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    });
  }
});

// Base URL (set via PHP inline script in header)
const BASE_URL =
  window.__BASE_URL || document.querySelector("base")?.href || "/dhoti-mahal/";
