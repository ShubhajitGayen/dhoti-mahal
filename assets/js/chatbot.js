/** @format */

document.addEventListener("DOMContentLoaded", () => {
  const root = document.getElementById("chatbot");
  if (!root) return;
  const bubble = document.getElementById("chatbotBubble");
  const windowEl = document.getElementById("chatbotWindow");
  const close = document.getElementById("chatbotClose");
  const messages = document.getElementById("chatbotMessages");
  const form = document.getElementById("chatbotForm");
  const input = document.getElementById("chatbotInput");
  const chips = document.getElementById("chatbotChips");
  let csrfToken = root.dataset.csrfToken || "";
  let whatsappUrl = "";

  const addTextMessage = (text, type) => {
    const message = document.createElement("div");
    message.className = `chatbot-message chatbot-message-${type}`;
    message.textContent = text;
    messages.appendChild(message);
    messages.scrollTop = messages.scrollHeight;
    return message;
  };

  const addCards = (cards) => {
    cards.forEach((card) => {
      const wrapper = document.createElement("div");
      wrapper.className = "chatbot-product-card";
      const image = document.createElement("img");
      image.src = card.image;
      image.alt = card.name;
      const details = document.createElement("div");
      const link = document.createElement("a");
      link.href = `${window.BASE_URL}pages/product.php?slug=${encodeURIComponent(card.slug)}`;
      link.textContent = card.name;
      const price = document.createElement("small");
      price.textContent = `₹${Number(card.effective_price).toFixed(2)} · ${card.stock_status}`;
      details.append(link, price);
      wrapper.append(image, details);
      messages.appendChild(wrapper);
    });
    messages.scrollTop = messages.scrollHeight;
  };

  const addWhatsApp = (url) => {
    if (!url) return;
    const link = document.createElement("a");
    link.className = "chatbot-fallback";
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener";
    link.textContent = "Continue on WhatsApp";
    messages.appendChild(link);
    messages.scrollTop = messages.scrollHeight;
  };

  const openChat = () => {
    windowEl.hidden = false;
    bubble.setAttribute("aria-expanded", "true");
    input.focus();
  };
  const closeChat = () => {
    windowEl.hidden = true;
    bubble.setAttribute("aria-expanded", "false");
  };
  bubble.addEventListener("click", openChat);
  close.addEventListener("click", closeChat);
  document.addEventListener("click", (event) => {
    if (!windowEl.hidden && !root.contains(event.target)) {
      closeChat();
    }
  });

  const sendMessage = async (value) => {
    const text = value.trim();
    if (!text || form.dataset.busy === "1") return;
    form.dataset.busy = "1";
    input.value = "";
    addTextMessage(text, "user");
    const pending = addTextMessage("Thinking...", "bot");
    form.querySelector("button").disabled = true;
    try {
      const response = await fetch(`${window.BASE_URL}api/chatbot.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "chat",
          message: text,
          csrf_token: csrfToken
        })
      });
      const result = await response.json();
      pending.remove();
      if (result.data?.csrf_token) csrfToken = result.data.csrf_token;
      if (result.data?.whatsapp_url) whatsappUrl = result.data.whatsapp_url;
      if (result.message) addTextMessage(result.message, "bot");
      if (Array.isArray(result.data?.cards)) addCards(result.data.cards);
      if (result.data?.fallback)
        addWhatsApp(result.data.whatsapp_url || whatsappUrl);
    } catch (error) {
      pending.remove();
      addTextMessage(
        "I'm unable to reply right now. Please contact us on WhatsApp.",
        "bot"
      );
      addWhatsApp(whatsappUrl);
    } finally {
      form.dataset.busy = "0";
      form.querySelector("button").disabled = false;
      input.focus();
    }
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    sendMessage(input.value);
  });

  chips.addEventListener("click", (event) => {
    const button = event.target.closest("button");
    if (!button) return;
    if (button.textContent === "Talk to human") {
      if (!whatsappUrl) whatsappUrl = `${window.BASE_URL}`;
      window.open(whatsappUrl, "_blank", "noopener");
      return;
    }
    sendMessage(button.textContent);
  });

  fetch(`${window.BASE_URL}api/chatbot.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "init", csrf_token: csrfToken })
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.data?.csrf_token) csrfToken = result.data.csrf_token;
      if (result.data?.whatsapp_url) whatsappUrl = result.data.whatsapp_url;
      if (result.data?.enabled === false) root.hidden = true;
    })
    .catch(() => {});
});
