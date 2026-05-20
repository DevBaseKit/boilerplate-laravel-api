# Boilerplate Laravel API

Boilerplate Laravel API yang sudah disiapkan untuk kebutuhan production baseline:
- JWT authentication
- API versioning (`/api/v1`)
- Standard response format
- Pagination custom payload
- Policy authorization (ownership + role)
- Request ID + structured logging + audit trail
- OpenAPI contract + automated tests

## Requirements

- PHP 8.2+
- Composer
- MySQL/PostgreSQL (atau DB lain yang didukung Laravel)

## Quick Start

1. Install dependency
```bash
composer install
```

2. Copy environment
```bash
cp .env.example .env
```

3. Generate app key
```bash
php artisan key:generate
```

4. Set konfigurasi database di `.env`

5. Run migration
```bash
php artisan migrate
```

6. (Opsional) Jalankan test
```bash
php artisan test
```

7. Jalankan server
```bash
php artisan serve
```

## Struktur Folder (Yang Paling Penting)

### Visual Structure

```text
app/
├── Constants/                  # Konstanta message & status code API
├── Http/
│   ├── Controllers/API/        # Controller endpoint API
│   ├── Middleware/             # Request ID & structured request logging
│   ├── Requests/API/           # Validasi request per endpoint
│   └── Resources/              # Transformer output response API
├── Models/                     # Eloquent models
├── Policies/                   # Authorization rule (ownership/role)
├── Repositories/API/           # Data access layer (query model)
├── Services/API/               # Business logic API
└── Traits/ApiResponseTrait.php # Format response sukses/error standar

database/
└── migrations/                 # Skema database

routes/
└── api.php                     # Definisi route API v1

tests/
└── Feature/                    # Integration / feature test API

openapi/
└── openapi.json                # Kontrak API manual
```

### Why This Structure

| Layer | Tanggung Jawab |
|---|---|
| Controller | Terima request, panggil service, kirim response |
| Request | Validasi input per endpoint |
| Service | Menjalankan business rules/use-case |
| Repository | Menangani query & akses data |
| Resource | Menstandarkan format output API |
| Policy | Menentukan siapa boleh akses data apa |
| Middleware | Observability, request tracing, logging |
| Tests | Menjaga kontrak API agar tidak regress |

### Benefit

- Struktur clean dan mudah dibaca tim baru.
- Mudah scaling karena pemisahan tanggung jawab jelas.
- Testable: business logic tidak tercampur di controller.
- Konsisten untuk endpoint baru: tinggal ikuti flow layer yang sama.

## Format Response

Contoh success:
```json
{
  "status": true,
  "status_code": 200,
  "message": "Success.",
  "result": {}
}
```

Contoh error:
```json
{
  "status": false,
  "status_code": 422,
  "message": "Error validation",
  "error_items": ["The name field is required."]
}
```

## Step-by-Step Membuat Endpoint API Baru

Contoh: bikin endpoint `Category`.

1. Buat Model + Migration
```bash
php artisan make:model Category -m
```
Isi migration, lalu:
```bash
php artisan migrate
```

2. Buat Request Validation
```bash
php artisan make:request API/StoreCategoryRequest
php artisan make:request API/UpdateCategoryRequest
php artisan make:request API/IndexCategoryRequest
```

3. Buat Resource Transformer
```bash
php artisan make:resource CategoryResource
```

4. Buat Repository Interface + Repository
- `app/Repositories/API/CategoryRepositoryInterface.php`
- `app/Repositories/API/CategoryRepository.php`

Minimal method:
- `paginate(int $perPage = 10)`
- `findOrFail(mixed $id)`
- `create(array $data)`
- `updateModel(Category $category, array $data)`
- `deleteModel(Category $category)`

5. Buat Service Interface + Service
- `app/Services/API/CategoryServiceInterface.php`
- `app/Services/API/CategoryService.php`

Isi business logic, panggil repository di sini.

6. Daftarkan binding di Service Provider
- `app/Providers/AppServiceProvider.php`

Tambahkan:
- `CategoryRepositoryInterface -> CategoryRepository`
- `CategoryServiceInterface -> CategoryService`

7. Buat Controller API
```bash
php artisan make:controller API/CategoryController --api
```
Di controller:
- Validasi lewat FormRequest
- Gunakan Service
- Return via `sendSuccess()` / `sendError()`
- Gunakan `CategoryResource`

8. Tambah Route
- `routes/api.php`
- Masukkan dalam group `prefix('v1')`
- Gunakan middleware yang sesuai (`auth:api`, throttle, dll)

Contoh:
```php
Route::middleware('auth:api')->group(function () {
    Route::apiResource('categories', CategoryController::class);
});
```

9. Tambah Policy (Jika Butuh Ownership/Role)
```bash
php artisan make:policy CategoryPolicy --model=Category
```
Register policy di `AppServiceProvider`.

10. Tambah Test Feature
- Test success case
- Test validation
- Test unauthorized/forbidden
- Test pagination contract

11. Update OpenAPI Contract
- Tambah path baru di `openapi/openapi.json`
- Pastikan response shape sesuai implementasi.

## Endpoint Utama Saat Ini

### Auth

#### `POST /api/v1/register`
- Auth: `No`
- Body params:
  - `name` (string, required)
  - `email` (string, required, unique)
  - `password` (string, required, confirmed)

#### `POST /api/v1/login`
- Auth: `No`
- Body params:
  - `email` (string, required)
  - `password` (string, required)

#### `POST /api/v1/logout`
- Auth: `Yes (Bearer Token)`
- Body params: `-`

#### `GET /api/v1/me`
- Auth: `Yes (Bearer Token)`
- Query params: `-`

#### `POST /api/v1/refresh`
- Auth: `Yes (Bearer Token)`
- Body params: `-`

### Products

#### `GET /api/v1/products`
- Auth: `Yes (Bearer Token)`
- Query params:
  - `page` (integer, optional, default `1`)
  - `limit` (integer, optional, default `10`, min `1`, max `100`)
  - `search` (string, optional) → search by `name` and `description`
  - `status` (string, optional, reserved for exact filter)
  - `order_by` (string, optional): `name|price|stock|created_at|updated_at`
  - `dir` (string, optional): `asc|desc` (default `desc`)

Contoh:
```http
GET /api/v1/products?search=iphone&limit=10&page=1&order_by=price&dir=asc
```

#### `POST /api/v1/products`
- Auth: `Yes (Bearer Token)` + policy (`admin` / `manager`)
- Body params:
  - `name` (string, required, max:255)
  - `description` (string, optional)
  - `price` (numeric, required, min:0)
  - `stock` (integer, required, min:0)

#### `GET /api/v1/products/{product}`
- Auth: `Yes (Bearer Token)` + policy (owner atau `admin`)
- Path params:
  - `product` (integer, required)

#### `PUT/PATCH /api/v1/products/{product}`
- Auth: `Yes (Bearer Token)` + policy (owner atau `admin`)
- Path params:
  - `product` (integer, required)
- Body params (semua optional, minimal kirim 1 field):
  - `name` (string, max:255)
  - `description` (string)
  - `price` (numeric, min:0)
  - `stock` (integer, min:0)

#### `DELETE /api/v1/products/{product}`
- Auth: `Yes (Bearer Token)` + policy (owner atau `admin`)
- Path params:
  - `product` (integer, required)

## Testing & Quality Check

Run semua test:
```bash
php artisan test
```

Generate OpenAPI otomatis dari source route:
```bash
php artisan openapi:generate
```
Output file:
- `openapi/openapi.generated.json`

Cek route:
```bash
php artisan route:list
```

Regenerate autoload:
```bash
composer dump-autoload
```
