# Laravel Realtime API

REST API sederhana menggunakan Laravel dengan JWT Authentication, Swagger/OpenAPI, PostgreSQL, dan integrasi data eksternal secara realtime.

## Tech Stack

* Laravel
* PHP 8.2+
* PostgreSQL
* JWT Authentication
* Swagger / OpenAPI
* REST API

## Features

* User Registration
* User Login menggunakan JWT
* Get authenticated user
* Logout
* Refresh JWT Token
* CRUD User
* Get data dari external API secara realtime
* Search data berdasarkan NAMA, NIM, dan YMD
* Swagger API Documentation

## External API

Data eksternal diambil dari:

`https://ogienurdiana.com/career/ecc694ce4e7f6e45a5a7912cde9fe131`

Data dari external API **tidak disimpan ke database**. Laravel mengambil data langsung dari sumber eksternal ketika endpoint dipanggil.

## Installation

Clone repository:

```bash
git clone <repository-url>
cd laravel-realtime-api
```

Install dependency:

```bash
composer install
```

Copy environment file:

```bash
cp .env.example .env
```

Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Generate JWT secret:

```bash
php artisan jwt:secret
```

## Database Configuration

Atur database PostgreSQL pada `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_realtime_api
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

Tambahkan URL external API:

```env
EXTERNAL_API_URL=https://ogienurdiana.com/career/ecc694ce4e7f6e45a5a7912cde9fe131
```

Jalankan migration:

```bash
php artisan migrate
```

## Run Application

Jalankan server:

```bash
php artisan serve
```

API dapat diakses melalui:

```text
http://127.0.0.1:8000
```

## API Endpoints

### Authentication Endpoint

| Method | Endpoint             | Description       |
| ------ | -------------------- | ----------------- |
| POST   | `/api/auth/register` | Register user     |
| POST   | `/api/auth/login`    | Login             |
| GET    | `/api/auth/me`       | Get current user  |
| POST   | `/api/auth/logout`   | Logout            |
| POST   | `/api/auth/refresh`  | Refresh JWT token |

### Users

| Method | Endpoint          | Description     |
| ------ | ----------------- | --------------- |
| GET    | `/api/users`      | Get all users   |
| POST   | `/api/users`      | Create user     |
| GET    | `/api/users/{id}` | Get user detail |
| PUT    | `/api/users/{id}` | Update user     |
| DELETE | `/api/users/{id}` | Delete user     |

### External Data

| Method | Endpoint               | Description              |
| ------ | ---------------------- | ------------------------ |
| GET    | `/api/external/data`   | Get latest external data |
| GET    | `/api/external/search` | Search external data     |

## Authentication

Endpoint yang membutuhkan authentication menggunakan JWT Bearer Token.

Tambahkan header:

```http
Authorization: Bearer YOUR_JWT_TOKEN
```

Contoh:

```http
GET /api/auth/me
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOi...
```

## Search External Data

Search berdasarkan nama:

```http
GET /api/external/search?nama=GALIH
```

Search berdasarkan NIM:

```http
GET /api/external/search?nim=123456
```

Search berdasarkan YMD:

```http
GET /api/external/search?ymd=2000-01-01
```

## Swagger Documentation

Swagger digunakan untuk dokumentasi dan testing API.

Setelah aplikasi berjalan, buka:

```text
http://127.0.0.1:8000/api/documentation
```

Jika dokumentasi belum dibuat, jalankan:

```bash
php artisan l5-swagger:generate
```

Kemudian gunakan tombol **Authorize** pada Swagger untuk memasukkan JWT token.

## Project Structure

```text
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php
│       ├── UserController.php
│       └── ExternalDataController.php
│
├── Models/
│   └── User.php
│
└── Services/
    └── ExternalApiService.php

config/
├── auth.php
├── jwt.php
└── services.php

database/
└── migrations/

routes/
└── api.php
```

## Notes

* PostgreSQL digunakan untuk menyimpan data user.
* Data dari external API tidak disimpan ke database.
* External API dipanggil secara langsung ketika endpoint external digunakan.
* JWT digunakan untuk authentication.
* Swagger digunakan sebagai API documentation dan testing tool.

## License

This project is created for learning and technical test purposes.
