# CertiMe

A full-featured digital credentialing platform built with PHP. Issue, manage, verify, and share tamper-proof digital certificates and Open Badges with cryptographic signing, PDF generation, and a comprehensive admin dashboard.

## Features

### Core Credentialing
- **Issue & Manage Credentials** — Create digital certificates with Ed25519 signatures and Merkle tree integrity proofs
- **Open Badge 3.0** — Full compliance with the Open Badges specification, including JSON-LD badge classes and badge image generation
- **PDF Certificates** — Generate branded PDF certificates with QR codes for instant verification (TCPDF)
- **Verification Portal** — Public verification page where anyone can validate a credential's authenticity by UID
- **Credential Endorsements** — Third-party endorsements with approval workflows

### Administration
- **Admin Dashboard** — User management, credential issuance, key management, audit logs
- **Super-Admin Panel** — Multi-tenant/institution management, feature flags, system health monitoring, CRL manager, garbage collection, dispute resolution, invoicing
- **Role-Based Access Control** — Six roles: `student`, `issuer`, `designer`, `viewer`, `moderator`, `admin`
- **Cryptographic Key Management** — Ed25519 keypair generation, PDF signing keys, key rotation with archived key verification
- **Bulk Issuance** — CSV upload for batch credential creation
- **Audit Trail** — Full audit logging of all administrative actions with IP tracking

### Integrations & API
- **REST API** — Full CRUD API with Bearer token authentication (`/api/v1/credentials`, `/api/v1/verify`)
- **Webhooks** — Configurable webhook endpoints with event broadcasting and delivery logs
- **LTI 1.3** — Learning Tools Interoperability integration for LMS platforms (Canvas, Moodle, etc.)
- **AI Agent** — Gemini-powered chat assistant for credential queries
- **OTP Badge Claiming** — One-time password system for recipients to claim credentials via email

### User Features
- **Portfolio** — Personal credential portfolio with export capabilities
- **Public Portfolio** — Customizable public profile page with shareable slug (`/p/username`)
- **Digital Resume** — Auto-generated resume from credentials with JSON and PDF export
- **Social Sharing** — Share credentials to LinkedIn, Twitter/X, Facebook with Open Graph metadata
- **Embeddable Badges** — Embed credential badges on external websites
- **Coursework Tracker** — Track and manage coursework linked to credentials
- **Evidence Linking** — Attach evidence (URLs, files) to credentials
- **Transcript View** — Academic transcript generation from earned credentials

### Platform
- **Badge Builder** — Visual drag-and-drop badge template designer
- **Badge Directory** — Public searchable directory of available badges
- **Skill Taxonomy** — Tag credentials with skills, browse by skill category
- **Plans & Pricing** — Subscription tiers with team management
- **Upload External Credentials** — Import credentials from other platforms

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.x |
| Database | SQLite (PDO) |
| Architecture | Custom MVC (Router → Controller → Model → View) |
| Crypto | Ed25519 (sodium), SHA-256, Merkle trees |
| PDF | TCPDF |
| QR Codes | bacon/bacon-qr-code |
| AI | Google Gemini API |
| Auth | Session-based with CSRF protection |
| Server | PHP built-in server / Apache / Nginx |

## Quick Start

### Prerequisites
- PHP 8.1+ with extensions: `pdo_sqlite`, `sodium`, `mbstring`, `curl`, `gd`
- Composer (optional — dependencies are vendored)

### Installation

```bash
# Clone the repository
git clone https://github.com/emircesur/certime.git
cd certime

# (Optional) Install dependencies via Composer
composer install

# Create environment file
cp .env.example .env
# Edit .env and set your GEMINI_API_KEY if you want AI features

# Start the development server
php -S localhost:8000 -t public public/index.php
```

The database is automatically created and migrated on first request (30 tables, seeded with skills, feature flags, and plans).

### Create Admin User

```bash
php scripts/create_admin.php
```

Default credentials: `admin` / `Admin@123`

### Generate Cryptographic Keys

```bash
php scripts/generate-keys.php
```

Or generate keys from the Admin → Keys page in the dashboard.

## Project Structure

```
certime/
├── public/              # Web root (index.php entry point)
│   └── assets/          # CSS, JS, images
├── app/
│   ├── core/            # Framework core (Router, Controller, Database, Config)
│   ├── controllers/     # 18 controllers handling all routes
│   ├── models/          # 10 data models (User, Credential, Plan, etc.)
│   ├── views/           # PHP templates organized by feature
│   └── lib/             # Libraries (Gemini, Agent, OpenBadge, MerkleTree)
├── data/                # Runtime data (SQLite DB, keys, sessions, portfolios)
├── scripts/             # CLI utilities (admin creation, key generation, migrations)
└── .env.example         # Environment configuration template
```

## API

### Authentication

Include your API key as a Bearer token:

```bash
curl -H "Authorization: Bearer YOUR_API_KEY" http://localhost:8000/api/v1/credentials
```

### Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/credentials` | List all credentials |
| `GET` | `/api/v1/credentials/:uid` | Get credential by UID |
| `POST` | `/api/v1/credentials` | Issue a new credential |
| `POST` | `/api/v1/credentials/:uid/revoke` | Revoke a credential |
| `GET` | `/api/v1/verify/:uid` | Verify a credential |
| `GET` | `/api/v1/user` | Get authenticated user info |

API keys can be created from Admin → API Keys.

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `GEMINI_API_KEY` | Google Gemini API key for AI chat agent | No |

## Scripts

| Script | Description |
|--------|-------------|
| `scripts/create_admin.php` | Create the default admin user |
| `scripts/generate-keys.php` | Generate Ed25519 signing keypairs |
| `scripts/generate_pdf_keys.php` | Generate PDF signing keys |
| `scripts/migrate_and_seed.php` | Run database migrations and seed data |
| `scripts/list_users.php` | List all users in the database |
| `scripts/list_uids.php` | List all credential UIDs |

## License

All rights reserved.
