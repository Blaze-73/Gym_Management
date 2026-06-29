# PayPal Sandbox — Real Developer Testing

This project uses **PayPal Checkout (REST API v2)** with redirect to PayPal Sandbox.

## Step 1 — Create Sandbox app

1. Go to [https://developer.paypal.com/dashboard/](https://developer.paypal.com/dashboard/)
2. **Apps & Credentials**
3. Open the **Sandbox** tab (not Live)
4. **Create App** → name: `Gym Management`
5. Copy:
   - **Client ID**
   - **Secret** (click Show — copy immediately; you cannot view it again)

## Step 2 — Configure `.env`

Edit `C:\xampp\htdocs\Gym_Management\.env`:

```env
PAYPAL_CLIENT_ID=AeA1QIZXiflr1_-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
PAYPAL_SECRET=ELxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
PAYPAL_MODE=sandbox
PAYPAL_CURRENCY=USD
PAYPAL_MOCK=false
FRONTEND_URL=http://localhost:5173
```

Important:

- Use **Sandbox** credentials only when `PAYPAL_MODE=sandbox`
- No quotes around values
- `PAYPAL_MOCK` must be **false** for real PayPal
- `FRONTEND_URL` must match your Vite URL (`npm run dev` → usually port 5173)

## Step 3 — Verify connection

```bash
cd C:\xampp\htdocs\Gym_Management
php artisan config:clear
php artisan paypal:verify
```

Expected output:

```
✅ Connected to PayPal sandbox API successfully.
```

If this fails, checkout will not work. Fix credentials before testing in the browser.

## Step 4 — Restart servers

```bash
php artisan serve
```

In another terminal:

```bash
cd frontend
npm run dev
```

## Step 5 — Test payment in browser

1. Log in as a **client** user
2. Open **Plans** → choose a plan → **Pay with PayPal**
3. You are redirected to **sandbox.paypal.com**
4. Log in with a **Sandbox Personal (Buyer)** account:
   - Developer Dashboard → **Testing Tools** → **Sandbox Accounts**
   - Use the email/password of a **Personal** account (not Business)
5. Approve the payment
6. You return to `/payment/success` → subscription activates
7. Check **My Subscription** in the sidebar

### Store checkout

Same flow from the cart → **Pay with PayPal**.

## API endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/payments/status` | Check if PayPal is configured |
| POST | `/api/payments/plan` | Start plan checkout |
| POST | `/api/payments/store` | Start store checkout |
| POST | `/api/payments/capture` | Complete payment after PayPal return |

## Troubleshooting

| Error | Solution |
|-------|----------|
| PayPal credentials missing | Fill `PAYPAL_CLIENT_ID` and `PAYPAL_SECRET` in `.env` |
| `invalid_client` on verify | Wrong Secret, or Live credentials with `PAYPAL_MODE=sandbox` |
| Mock mode enabled | Set `PAYPAL_MOCK=false` |
| Capture fails / token invalid | Do not refresh success URL; start a new checkout |
| Redirect goes to wrong port | Set `FRONTEND_URL` to your Vite URL |

## Go live (production)

1. Create a **Live** app under **Live** tab in PayPal Dashboard
2. Update `.env`:

```env
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=<live_client_id>
PAYPAL_SECRET=<live_secret>
FRONTEND_URL=https://your-production-domain.com
```
