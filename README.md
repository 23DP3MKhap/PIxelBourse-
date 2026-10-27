# PIxelBoris

Tiešsaistes klikera spēle ar iekšējo attēlu tirgu.

- `backend/` – Laravel API
- `frontend/` – Vue lietotne

## Kas jāuzinstalē

- **PHP 8.3+ un Composer**. Windows datorā vienkāršākais variants ir [Laravel Herd](https://herd.laravel.com/windows): uzinstalē to un atver vienu reizi, tad tas pats lejupielādē PHP un Composer.
- **Node.js 20+** – https://nodejs.org (pārbaudi ar `node -v`).
- **Cloudinary konts** – bezmaksas, tur glabājas augšupielādētie attēli (skat. zemāk).

## Cloudinary API atslēga

1. Reģistrējies https://cloudinary.com (bezmaksas plāns ir pietiekams).
2. Pēc pieslēgšanās atver **Dashboard** (vai **Settings → API Keys**).
3. Atrodi lauku **API environment variable**. Tas izskatās šādi:
   ```
   CLOUDINARY_URL=cloudinary://123456789012345:AbCdEfGhIjKlMnOp@mans-cloud-name
   ```
4. Nokopē visu rindu un ieliec to failā `backend/.env` (`.env` izveido 1. palaišanas solī).

`.env` failu **nekad necommito** – tajā ir slepenā atslēga. Ja `CLOUDINARY_URL` nav, attēlu augšupielāde atgriež kļūdu 500.

## Pirmā palaišana

**Backend** (mapē `backend/`):

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

Pēc `cp` atver `backend/.env` un ieraksti savu `CLOUDINARY_URL` (skat. augstāk).

**Frontend** (mapē `frontend/`):

```sh
cd frontend
npm install
```

## Palaišana katru reizi

Vajag **divus termināļus**, abiem jādarbojas vienlaicīgi:

| Terminālis | Mape | Komanda | Adrese |
|---|---|---|---|
| 1 | `backend/` | `php artisan serve` | http://localhost:8000 |
| 2 | `frontend/` | `npm run dev` | http://localhost:5173 |

Lietotni atver pārlūkā: **http://localhost:5173**. Frontend pats pārsūta `/api` pieprasījumus uz backend (`vite.config.js`).

## Pēc `git pull`

Ja kāds ir pievienojis pakotnes vai migrācijas:

```sh
cd backend
composer install
php artisan migrate

cd ../frontend
npm install
```

`php artisan migrate:fresh --seed` izdzēš visus lokālos datus un izveido testa datus no jauna.

## Testa konti

Visiem parole `password`:

| E-pasts | Loma |
|---|---|
| `test@example.com` | lietotājs (5000 monētas) |
| `admin@example.com` | administrators |
| `artist@example.com` | lietotājs ar attēliem tirgū |
| `collector@example.com` | lietotājs ar attēliem tirgū |

## Biežākās problēmas

- **`php` vai `composer` netiek atrasts** – aizver un atver termināli (vai VS Code) no jauna pēc Herd instalēšanas.
- **Attēlu augšupielāde – kļūda 500** – `backend/.env` trūkst `CLOUDINARY_URL`.
- **Frontend rāda kļūdu visiem pieprasījumiem** (piem. "Pieprasījums neizdevās (502)") – nav palaists backend (`php artisan serve`).
- **Kļūda par trūkstošu kolonnu/tabulu** – palaid `php artisan migrate`.

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