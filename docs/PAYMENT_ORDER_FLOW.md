# Payment, Orders & Tracking — Flow Guide

## Database (add-only)

| Table | Purpose |
|-------|---------|
| `payments` | All PayPal transactions (plan + store) |
| `subscriptions` | Plan subscriptions with customer info |
| `orders` | Store orders (existing table + new columns) |
| `order_items` | Line items (unchanged) |

**New columns on `orders`:** `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `payment_status`

**New columns on `subscriptions`:** `customer_name`, `customer_email`, `customer_phone`, `billing_address`

---

## 1. Plan payment flow

```
Select plan → Fill contact form → Pay with PayPal → Sandbox approve
→ /payment/success → API capture → subscription paid + membership active
```

- Only **one** active paid subscription per user
- `transaction_id` stored on `payments` (duplicate blocked)

---

## 2. Store payment flow

```
Add to cart → Continue to Checkout → Contact form → Pay with PayPal
→ Capture → order created (status: pending, payment_status: paid) + order_items
```

Stock is reduced when payment succeeds.

---

## 3. Order tracking flow (client)

- **My Orders** (`/my-orders`): list orders + **Track Order** panel
- Status steps: **Pending** (yellow) → **Shipped** (blue) → **Delivered** (green)

---

## 4. Admin management flow

- **Admin → Orders** (`/admin/orders`)
  - **Store Orders** tab: all orders, update status dropdown
  - **Plan Payments** tab: subscriptions + amounts + dates

Only users with `role:admin` can access admin routes.

---

## API endpoints

| Role | Method | Endpoint |
|------|--------|----------|
| Client | POST | `/api/payments/plan` (+ customer fields) |
| Client | POST | `/api/payments/store` (+ customer fields) |
| Client | GET | `/api/orders` |
| Client | GET | `/api/subscriptions/me` |
| Admin | GET | `/api/admin/store-orders` |
| Admin | PUT | `/api/admin/store-orders/{id}/status` |
| Admin | GET | `/api/admin/plan-payments` |

---

## Subscription management

| Role | Feature |
|------|---------|
| Client | **My Subscription** → terminate anytime |
| Client | Bell notification when plan expires in **7 days** |
| Admin | **Subscriptions** (`/admin/subscriptions`) → view all, terminate paid active plans |

---

## Setup

```bash
php artisan migrate
php artisan serve
cd frontend && npm run dev
```

PayPal Sandbox: see `docs/PAYPAL_SETUP.md`
