# Translation Management System

A Laravel-based Translation Management System built for high-performance multilingual support with caching, search, and export APIs.

---

## 🚀 Features

- 🔑 **Authentication** (Register, Login, Me) with Laravel Sanctum  
- 📦 **Translation Management** (CRUD operations, search with filters)  
- ⚡ **High-Performance Export API** with caching and optimized queries  
- 🏎️ Handles **100K+ translations** with fast lookups (<400ms goal)  
- 🗂️ Tag-based filtering using JSON fields  
- 🛠️ Unit & Feature tests for critical functionalities  
- 📊 Performance benchmark tests included  
- 📜 Swagger/OpenAPI documentation included  

---

## 🛠️ Setup Instructions

### 1. Clone Repository
```bash
git clone https://github.com/furqanasad110/translation-digitaltolk.git
cd translation-digitaltolk
```

### 2. Install Dependencies
```bash
composer install
npm install && npm run dev
```

### 3. Environment Setup
Copy `.env.example` to `.env` and update database credentials:
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` for Sanctum, DB, and caching (Redis recommended).

### 4. Run Migrations & Seeders
```bash
php artisan migrate 
```
(Optional) `php artisan db:seed --class=\Database\Seeders\BigTranslationSeeder`

Seeder creates 100K+ translations for testing performance.

### 5. Run Tests
```bash
php artisan test
```

---

## 📡 API Endpoints

### Auth
| Method | Endpoint         | Description |
|--------|-----------------|-------------|
| POST   | `/api/register` | Register user |
| POST   | `/api/login`    | Login and get token |
| GET    | `/api/me`       | Get logged-in user |

### Translations (Protected by `auth:sanctum`)
| Method | Endpoint              | Description |
|--------|-----------------------|-------------|
| POST   | `/api/translations`   | Create translation |
| PUT    | `/api/translations/{id}` | Update translation |
| GET    | `/api/translations/{id}` | Get translation by ID |
| GET    | `/api/translations`   | Search translations (filters: key, locale, context, tag) |

### Export
| Method | Endpoint                  | Description |
|--------|---------------------------|-------------|
| GET    | `/api/export/{locale}.json` | Export translations for given locale (supports context filter) |

---

## 📊 Performance & Testing

- Optimized with **cache versioning** to invalidate old exports  
- Export API tested against **100K+ records**  
- Performance goal: response time < **400ms** (with Redis + indexing)  
- ✅ Unit tests for repository logic  
- ✅ Feature tests for Auth, CRUD, Export  

---

## 🎨 Design Choices

- **Repository pattern** isolates data access and allows easy swapping of storage backends.
- **Laravel Sanctum** for lightweight API auth  
- **JSON column for tags** → allows flexible tag-based filtering  
- **Chunked queries** for memory-safe large exports  
- **Simple Pagination** for faster searches on huge datasets
- **Indexes** on locale, context and unique constraint on (key, locale, context) for fast lookups.
- **tags** stored as JSON array for flexible filtering via `whereJsonContains`.
- **Export caching**: a translations_version key is incremented on writes and used to create versioned cache keys for export. This enables CDN-friendly cache headers and instant invalidation.
- **Seeder**: batched inserts to create 100k+ records without blowing up memory.

---

- Performance guarantees (<200ms endpoints, <400ms export) depend on production hardware, DB tuning, Redis enabled, and PHP-FPM + web server. The code is optimized to help meet these constraints (indexes, JSON columns, batched inserts, caching).
