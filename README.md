# PIxelBoris

Online clicker game with an internal image market.

- `backend/` – Laravel 13 JSON API (auth via Laravel Sanctum tokens)
- `frontend/` – Vue 3 SPA

## Backend setup

Requires PHP 8.3+, Composer and Node 22.18+ (on Windows, [Laravel Herd](https://herd.laravel.com) installs PHP + Composer).

```sh
cd backend
composer install
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve          # http://localhost:8000
php artisan test           # run the test suite
```

The `composer require` / `vendor:publish` lines are only needed once (they add Sanctum and its
`personal_access_tokens` migration); commit the resulting changes.

Seeded accounts (password `password`): `test@example.com` (user), `admin@example.com` (admin).

## API – users

Send `Accept: application/json` on every request. Authenticated routes need
`Authorization: Bearer <token>`, where the token comes from register/login.
Validation errors return `422` with an `errors` object keyed by field.

| Method | URL | Auth | Body | Response |
|---|---|---|---|---|
| POST | `/api/auth/register` | – | `username`, `email`, `password`, `password_confirmation` | `201 {token, user}` |
| POST | `/api/auth/login` | – | `email`, `password` | `200 {token, user}` |
| POST | `/api/auth/logout` | user | – | `204` |
| GET | `/api/user` | user | – | `200 {data: user}` |
| PATCH | `/api/user` | user | `username?`, `email?` | `200 {data: user}` |
| PUT | `/api/user/password` | user | `current_password`, `password`, `password_confirmation` | `204` (logs out other devices) |
| DELETE | `/api/user` | user | `password` | `204` |
| GET | `/api/admin/users?search=&role=&page=` | admin | – | `200 {data: [user], links, meta}` (20 per page) |
| GET | `/api/admin/users/{id}` | admin | – | `200 {data: user}` |
| PATCH | `/api/admin/users/{id}` | admin | `username?`, `email?`, `role?` (`user`/`admin`) | `200 {data: user}` |
| DELETE | `/api/admin/users/{id}` | admin | – | `204` |

User object: `{id, username, email, coin_balance, role, created_at, updated_at}`.

Rules: username 3–50 letters/digits, unique; email unique; password ≥ 8 characters with letters,
digits and a symbol. `coin_balance` and `role` can never be set by the user themselves. Register/login are
limited to 6 requests per minute. Admins can't delete themselves or remove their own admin role.
