# PIUS Academy - Backend (Laravel)

Laravel aplikacija za upravljanje registracijom studenata, ugovorima, fakturama i plaćanjima.

## Instalacija

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ContractTemplateSeeder
```

## Pokretanje

```bash
php artisan serve
# http://localhost:8000
```

## Struktura

```
app/
├── Http/
│   ├── Controllers/Api/    # API kontroleri
│   └── Requests/           # Form validacija
├── Models/                 # Eloquent modeli
└── Mail/                   # Email klase

config/                     # Konfiguracija
database/
├── migrations/             # Migracije
└── seeders/                # Seederi

routes/
├── api.php                 # API rute
└── web.php                 # Web rute

storage/                    # Fajlovi i logovi
```

## API Endpoints

### Javni
- `POST /api/students` - Registracija
- `GET /api/contracts/preview` - Pregled ugovora
- `POST /api/contracts` - Potpisivanje

### Admin
- `POST /api/auth/login` - Login
- `GET /api/students` - Lista studenata
- `GET /api/contracts` - Lista ugovora
- `GET /api/invoices` - Lista faktura

## Konfiguracija

Uredite `.env` fajl:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=pius_academy
DB_USERNAME=pius_user
DB_PASSWORD=secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
```

## Migracije

```bash
# Pokrenite migracije
php artisan migrate

# Seedujte template-e
php artisan db:seed --class=ContractTemplateSeeder
```

## Testiranje

```bash
php artisan test
```

## Podrška

Za pitanja, kontaktirajte: info@pius-academy.com
