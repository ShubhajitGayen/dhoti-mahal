# WhatsApp Automation System - Setup Guide

## Overview
This WhatsApp automation system enables you to send automatic order notifications to customers from the admin panel. No API costs initially - it uses WhatsApp Web links that open the chat interface.

## What's Installed

### 1. **Database Tables**
- `whatsapp_messages` - Tracks all messages sent to customers
- `whatsapp_templates` - Pre-written message templates for different scenarios

### 2. **Pre-Built Templates** (7 templates)
✅ Order Confirmation - Welcome message after order placement
✅ Order Processing - Notify when order is being processed
✅ Order Shipped - Send tracking info and delivery estimate
✅ Order Delivered - Confirmation that order arrived
✅ Order Delayed - Notify about any delays
✅ Payment Reminder - For pending payments
✅ Custom Message - Send personalized messages

### 3. **Files Created**
- `/config/migrations.sql` - Database schema
- `/includes/whatsapp-helpers.php` - Backend messaging functions
- `/api/whatsapp.php` - API endpoint for message actions
- `/assets/js/whatsapp.js` - Frontend messaging logic

### 4. **Updated Files**
- `/admin/order-detail.php` - New messaging panel with quick-send buttons

---

## How to Use

### 📦 Quick Send Templates

From the Admin Order Detail page, you'll see 5 quick template buttons in the right panel:
1. **Order Confirmed** - Sends welcome message
2. **Processing** - Updates customer order is being prepared
3. **Shipped** - Sends with tracking info
4. **Delivered** - Confirmation message
5. **Payment Reminder** - For pending payments

**Steps:**
1. Go to Admin → Orders → Click on any order
2. Scroll down to "WhatsApp Messages" section
3. Click the template button you want to send
4. A preview will appear - review and click OK
5. WhatsApp Web will open showing the message ready to send
6. Click Send button in WhatsApp

### ✍️ Custom Messages

Send any personalized message:
1. Click "Custom Message" button
2. Type your message
3. Review the preview
4. Confirm and send

### 💬 Direct Chat

Use "Open Chat" to directly message the customer without using templates.

---

## Advanced Features (Future Upgrades)

### Option 1: Automatic Sending (Medium Priority)
To make messages send automatically when order status changes:
```
Set up a webhook that triggers when status updates → auto-send WhatsApp message
Requires: Cron job or Event listener
Cost: None
```

### Option 2: Twilio Integration (Higher Scale)
When you get more orders, integrate Twilio for:
- Automatic sending (no manual clicking)
- Better tracking
- SMS fallback
- Cost: ~₹0.01 per message

### Option 3: WhatsApp Business API (Enterprise)
Direct WhatsApp integration for:
- Verified green checkmark
- Better deliverability
- Cost: ₹20-30 per month + messaging costs

---

## Managing Templates

### View & Edit Templates
Admin panel → Settings → WhatsApp Templates (Feature to be added)

Available variables in templates:
- `{customer_name}` - Customer's full name
- `{order_number}` - Order ID
- `{order_total}` - Total amount
- `{order_status}` - Current status
- `{tracking_link}` - Direct tracking URL
- `{tracking_id}` - Courier tracking ID
- `{delivery_date}` - Expected delivery date
- `{carrier_name}` - Courier company name

### Create Custom Template
SQL Query:
```sql
INSERT INTO whatsapp_templates (key_name, title, template, description, variables, is_active)
VALUES (
  'my_template',
  'My Custom Template',
  'Hi {customer_name}, Your order #{order_number} status: {order_status}',
  'My custom template description',
  '["customer_name", "order_number", "order_status"]',
  1
);
```

---

## Testing

### Test a Message
1. Place a test order on your store
2. Go to that order in admin
3. Click a template button
4. Send the message

### Verify in Database
```sql
SELECT * FROM whatsapp_messages;
SELECT * FROM whatsapp_templates;
```

---

## Troubleshooting

### Message Preview Shows Blank Variables
**Issue:** Variables like `{customer_name}` showing in preview
**Fix:** Ensure order has guest_name, guest_phone, etc. filled in

### Can't Send Message
**Likely Causes:**
- Order doesn't have customer phone number
- Template doesn't exist in database
- Admin not logged in
**Check:** PHP error logs in `/storage/logs.txt`

### WhatsApp Link Opens but No Message
**Issue:** Message field is empty in WhatsApp
**Reason:** Phone number format issue or special characters
**Fix:** System auto-formats phone numbers (adds country code if needed)

---

## Best Practices

✅ **DO:**
- Use appropriate template for each order status
- Customize messages with order details
- Follow up with customers within 24 hours
- Use WhatsApp for urgent updates

❌ **DON'T:**
- Send too many messages (max 2-3 per customer)
- Use WhatsApp for complaints (use phone call or email)
- Store sensitive payment info in messages

---

## Next Steps

### For More Features:
1. **Automatic Sending** - Set up status change triggers
2. **Template Management UI** - Create admin panel to edit templates
3. **Message Analytics** - Track which templates work best
4. **Scheduled Sending** - Send messages at specific times
5. **Bulk Messaging** - Send to multiple customers at once

### To Integrate with Twilio (When Ready):
Contact Twilio for WhatsApp Business API access:
- Website: twilio.com/whatsapp
- Cost: ~$0.01 USD per message
- Benefit: Automatic sending, better reliability, two-way messaging

---

## Support

For issues or questions about the WhatsApp system:
1. Check Database: `SELECT * FROM whatsapp_messages WHERE order_id = YOUR_ORDER_ID;`
2. Check Logs: Review `/storage/logs.txt`
3. Test Variables: Edit `/admin/order-detail.php` and check order data

---

**System Status:** ✅ Ready to Use!
**Version:** 1.0
**Last Updated:** March 2026
