# PIxelBoris

Tiešsaistes klikera spēle ar iekšējo attēlu tirgu.

- `backend/` – Laravel API
- `frontend/` – Vue lietotne

## Backend palaišana

Vajag PHP un Composer.

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Backend http://localhost:8000.

Testa konti (parole `password`):
- `test@example.com` – lietotājs
- `admin@example.com` – administrators

## Lietotāju API

Katram pieprasījumam jāsūta galvene `Accept: application/json`.
Pēc reģistrācijas vai pieslēgšanās saņem tokenu, un tas jāsūta galvenē
`Authorization: Bearer <token>`.

| Metode | Adrese | Kas var | Ko sūtīt |
|---|---|---|---|
| POST | `/api/auth/register` | visi | `username`, `email`, `password`, `password_confirmation` |
| POST | `/api/auth/login` | visi | `email`, `password` |
| POST | `/api/auth/logout` | lietotājs | – |
| GET | `/api/user` | lietotājs | – |
| PATCH | `/api/user` | lietotājs | `username`, `email` |
| PUT | `/api/user/password` | lietotājs | `current_password`, `password`, `password_confirmation` |
| DELETE | `/api/user` | lietotājs | `password` |
| GET | `/api/admin/users` | admins | meklēšana: `?search=`, `?role=` |
| GET | `/api/admin/users/{id}` | admins | – |
| PATCH | `/api/admin/users/{id}` | admins | `username`, `email`, `role` |
| DELETE | `/api/admin/users/{id}` | admins | – |

Noteikumi:
- lietotājvārds 3–50 burti vai cipari, unikāls;
- parole vismaz 8 rakstzīmes ar burtiem, cipariem un simbolu;
- monētas (`coin_balance`) un lomu (`role`) lietotājs pats mainīt nevar.
