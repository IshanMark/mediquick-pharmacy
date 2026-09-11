# MediQuick Pharmacy

CSE4206 (Web Application Development) coursework — an online pharmacy web application for
**MediQuick Pharmacy**, a fictional pharmacy based in Kurunegala, Sri Lanka. Built with plain
PHP 8, MySQL and Bootstrap 5, designed to run on XAMPP.

Three separated areas share one codebase: **customer** (shop, cart, checkout, prescriptions),
**staff** (prescription review, order fulfilment, stock), and **admin** (staff accounts,
reports, audit log).

## Repository contents

| Path | What it is |
|---|---|
| [mediquick/](mediquick/) | The application itself — see [mediquick/README.md](mediquick/README.md) for setup |
| [database/schema.sql](database/schema.sql) | MySQL schema — 12 tables |
| [database/seed.sql](database/seed.sql) | Sample data + 3 test accounts |
| [docs/mediquick-plan.html](docs/mediquick-plan.html) | PRD, UML/DFD diagrams, DB design pack |
| [docs/how-to-run-si.md](docs/how-to-run-si.md) | Full setup guide in **Sinhala** (සිංහල) |
| `mediquick_pharmacy_web_application.html` | Original UI mockup the app's styling is based on |

## Quick start

See [mediquick/README.md](mediquick/README.md) for full setup steps (XAMPP install, database
import, test logins). In short:

1. Copy `mediquick/` into `C:\xampp\htdocs\`
2. Import `database/schema.sql` then `database/seed.sql` via phpMyAdmin
3. Open `http://localhost/mediquick/`

Sinhala speakers: see [docs/how-to-run-si.md](docs/how-to-run-si.md) for the same steps with a
full introduction, written in Sinhala.

## Tech stack

PHP 8 · MySQL · Bootstrap 5 · XAMPP (Apache + PHP + MySQL) · PayHere sandbox · Chart.js
