# MediQuick Pharmacy — setup

Plain PHP 8 + MySQL + Bootstrap 5, built to run on XAMPP. No framework, no build step.

## 1. Install

1. Copy this `mediquick` folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, create nothing manually — instead use **Import**:
   - Import `database/schema.sql` first (creates `mediquick_db` and all 12 tables).
   - Import `database/seed.sql` second (adds sample products, articles and 3 test accounts).
4. Visit `http://localhost/mediquick/`.

If your MySQL root user has a password, or you installed under a different folder name, edit
`includes/config.php` — `DB_USER`/`DB_PASS` and nothing else needs to change; `BASE_URL` is
computed automatically from wherever the folder lives under `htdocs`.

## 2. Test accounts (from seed.sql)

| Role     | Email                 | Password    |
|----------|------------------------|-------------|
| Admin    | admin@mediquick.lk     | Password123 |
| Staff    | staff@mediquick.lk     | Password123 |
| Customer | customer@example.com   | Password123 |

## 3. Areas

- **Public / customer** — root `.php` files (`index.php`, `shop.php`, `cart.php`, `checkout.php`, …) and `account/`.
- **Staff** — `staff/` (prescription review, orders, products, inquiries). Requires role `staff` or `admin`.
- **Admin** — `admin/` (staff accounts, reports, categories, audit log). Requires role `admin`.
- **Shared code** — `includes/` (db, auth, csrf, helpers, page chrome). One `assets/css/style.css` for all three areas.

## 4. Payment

Cash on delivery works immediately, no setup needed. Card payment goes through the
**PayHere sandbox**: create a free sandbox merchant account at https://sandbox.payhere.lk,
then put your Merchant ID and Merchant Secret into `includes/config.php`
(`PAYHERE_MERCHANT_ID`, `PAYHERE_MERCHANT_SECRET`). Without real sandbox credentials the
card option will redirect but PayHere will reject the request — use COD to demo checkout
end to end, and show the PayHere code (`checkout.php`, `payhere/pay.php`, `payhere/notify.php`)
in your report as the implementation.

## 5. What was simplified (state clearly in your report as assumptions)

- Product photos are a placeholder icon, not real uploaded images — keeps the build focused
  on the required back-end logic rather than a file-upload feature the brief doesn't ask for.
- A prescription must already be **approved** before it can be attached at checkout, rather
  than creating a "pending" order that blocks on review. Simpler flow, same safety guarantee
  (no Rx-only item ships without pharmacist sign-off).
- No SMS/email — notifications are in-app only (see the bell/list in `account/notifications.php`).

## 6. Checking your own changes

Run this after editing any `.php` file — it catches typos before you open the browser:

```
php -l path\to\file.php
```

Or lint everything at once from the `mediquick` folder (Git Bash):

```
find . -name "*.php" -exec php -l {} \;
```
