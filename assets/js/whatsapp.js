/**
 * Dhoti Mahal - WhatsApp Messaging System
 * Handles WhatsApp integration for order notifications
 *
 * @format
 */

class WhatsAppMessenger {
  constructor(orderId) {
    this.orderId = orderId;
    this.apiUrl =
      window.WHATSAPP_API_URL || (window.BASE_URL || "/") + "api/whatsapp.php";
    this.templates = [];
    this.currentMessage = "";
  }

  /**
   * Initialize the messenger and load templates
   */
  async init() {
    try {
      const response = await fetch(this.apiUrl + "?action=get_templates");
      const data = await response.json();
      if (data.success) {
        this.templates = data.templates;
      }
    } catch (error) {
      console.error("Failed to load WhatsApp templates:", error);
    }
  }

  /**
   * Send a predefined template message
   */
  async sendTemplate(templateKey, showPreviewFirst = true) {
    try {
      // Get preview first
      const previewResponse = await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=get_template_preview&order_id=${this.orderId}&template_key=${templateKey}`,
      });

      if (!previewResponse.ok) {
        let errText = previewResponse.statusText;
        try {
          const errJson = await previewResponse.json();
          if (errJson && errJson.error) errText = errJson.error;
        } catch (e) {}
        throw new Error(`API Error ${previewResponse.status}: ${errText}`);
      }

      const previewData = await previewResponse.json();

      if (!previewData.success) {
        alert("Error: " + (previewData.error || "Failed to generate preview"));
        return false;
      }

      if (showPreviewFirst) {
        // Show preview modal
        if (
          !confirm(
            "Message Preview:\n\n" +
              previewData.preview +
              "\n\nSend this message?",
          )
        ) {
          return false;
        }
      }

      // Send the message
      const sendResponse = await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=send_template&order_id=${this.orderId}&template_key=${templateKey}`,
      });

      if (!sendResponse.ok) {
        let errText = sendResponse.statusText;
        try {
          const errJson = await sendResponse.json();
          if (errJson && errJson.error) errText = errJson.error;
        } catch (e) {
          // ignore parse errors
        }
        throw new Error(`API Error ${sendResponse.status}: ${errText}`);
      }

      const sendData = await sendResponse.json();

      if (sendData.success) {
        alert("Message sent! Opening WhatsApp...");
        // Open WhatsApp link
        if (sendData.link) {
          window.open(sendData.link, "_blank");
        }
        return true;
      } else {
        alert("Error: " + (sendData.error || "Unexpected response"));
        return false;
      }
    } catch (error) {
      console.error("Error sending template:", error);
      alert(`Failed to send message: ${error.message}`);
      return false;
    }
  }

  /**
   * Send a custom message
   */
  async sendCustom(message) {
    if (!message || message.trim() === "") {
      alert("Please enter a message");
      return false;
    }

    try {
      // Show preview
      if (
        !confirm("Message Preview:\n\n" + message + "\n\nSend this message?")
      ) {
        return false;
      }

      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=send_custom&order_id=${this.orderId}&message=${encodeURIComponent(message)}`,
      });

      if (!response.ok) {
        let errText = response.statusText;
        try {
          const errJson = await response.json();
          if (errJson && errJson.error) errText = errJson.error;
        } catch (e) {}
        throw new Error(`API Error ${response.status}: ${errText}`);
      }

      const data = await response.json();

      if (data.success) {
        alert("Message sent! Opening WhatsApp...");
        if (data.link) {
          window.open(data.link, "_blank");
        }
        return true;
      } else {
        alert("Error: " + (data.error || "Unexpected response"));
        return false;
      }
    } catch (error) {
      console.error("Error sending custom message:", error);
      alert(`Failed to send message: ${error.message}`);
      return false;
    }
  }

  /**
   * Get message history for the order
   */
  async getHistory() {
    try {
      const response = await fetch(
        this.apiUrl + "?action=get_message_history&order_id=" + this.orderId,
      );
      const data = await response.json();
      if (data.success) {
        return data.messages;
      }
      return [];
    } catch (error) {
      console.error("Error fetching history:", error);
      return [];
    }
  }

  /**
   * Display templates in a dropdown
   */
  getTemplateSelect() {
    let html =
      '<select id="whatsapp-template-select" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:3px;font-size:14px;">';
    html += '<option value="">-- Select a template --</option>';

    this.templates.forEach((template) => {
      html += `<option value="${template.key_name}">${template.title}</option>`;
    });

    html += "</select>";
    return html;
  }

  /**
   * Format a message for display (convert newlines to <br>)
   */
  static formatMessageForDisplay(message) {
    return message.replace(/\n/g, "<br>");
  }
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
  // Check if this is an order detail page
  const orderId = document
    .querySelector("[data-order-id]")
    ?.getAttribute("data-order-id");
  if (orderId) {
    window.whatsappMessenger = new WhatsAppMessenger(orderId);
    window.whatsappMessenger.init();
  }
});

// Helper function to send quick template
function sendWhatsAppTemplate(templateKey) {
  if (window.whatsappMessenger) {
    window.whatsappMessenger.sendTemplate(templateKey, true);
  }
}

// Helper function to send custom message
function openCustomWhatsAppForm() {
  const message = prompt("Enter your message:");
  if (message) {
    if (window.whatsappMessenger) {
      window.whatsappMessenger.sendCustom(message);
    }
  }
}
