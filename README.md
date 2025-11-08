# Coral Geo Zones

Coral is a Laravel 10 + Livewire 2 application that lets analysts draw custom maritime geozones, assign them to high-level categories (War Risk, Country, Port), and review them on interactive Leaflet maps. Geometry is stored as `geometry(MultiPolygon, 4326)` in PostGIS and validated/normalized on save, so the data is immediately usable in downstream spatial workflows.

## Key Features

- Create, edit, and view user-defined geozones on a Leaflet + leaflet-draw map.
- Store geometries as SRID 4326 MultiPolygons with automatic validation via PostGIS (`ST_IsValid`/`ST_MakeValid`).
- Category reference table (War Risk, Country, Port) with cascading relations to zones.
- Livewire-powered list with search, category filtering, pagination, and sortable columns.
- Bootstrap UI plus Mix-built CSS/JS  with preconfigured Leaflet assets.

## Requirements

| Dependency | Notes |
| --- | --- |
| PHP 8.2+ | With common extensions enabled (`bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, etc.). |
| Composer 2 | For installing PHP dependencies. |
| Node.js 18+ & npm 9+ | For building the frontend bundle with Laravel Mix. |
| PostgreSQL 14+ with PostGIS | The `geo_zones.geometry` column requires the PostGIS extension. |

> **PostGIS**: Ensure the target database has `CREATE EXTENSION postgis;` (or `CREATE EXTENSION IF NOT EXISTS postgis;`) executed once before running the migrations.

## Local Setup

```bash
# 1. Clone the repo
 git clone https://github.com/your-org/coral.git
 cd coral

# 2. Install PHP dependencies
 composer install

# 3. Copy the environment file and set your values
 cp .env.example .env
 # Update the DB_*, APP_URL, etc. to match your environment.
 # Example PostgreSQL block:
 # DB_CONNECTION=pgsql
 # DB_HOST=127.0.0.1
 # DB_PORT=5433
 # DB_DATABASE=coral
 # DB_USERNAME=postgres
 # DB_PASSWORD=postgres

# 4. Generate the application key
 php artisan key:generate

# 5. Install frontend dependencies
 npm install

# 6. Build assets once (for production builds use npm run build)
 npm run dev

# 7. Create the application and testing databases (PostgreSQL)
php artisan db:create             # uses values from .env
php artisan db:create --env=testing  # uses values from .env.testing

# 8. Run database migrations + seeders (requires PostGIS)
php artisan migrate --seed

# 9. Start the dev servers
php artisan serve           # Laravel HTTP server (http://127.0.0.1:8000)
npm run watch               # Optional: recompile assets on change
```

## Database & Seed Data

- `database/migrations` contains the schema for `categories` and `geo_zones`. The latter adds the spatial column + GIST index via raw SQL.
- `CategorySeeder` loads the canonical categories (War Risk, Country, Port).
- `GeoZoneSeeder` inserts two demo polygons so the UI has data immediately.
- Running `php artisan migrate --seed` will drop/create tables (if `migrate:fresh`) and populate both category + demo zone tables.

If you later need a clean slate:
```bash
php artisan migrate:fresh --seed
```

> After seeding you should see “Demo Gulf Zone” and “Demo Lagos Zone” plus the three categories in the UI—handy for quick sanity checks.

## Livewire Components & Routes

| Component | Route | Purpose |
| --- | --- | --- |
| `GeoZones\ListZones` | `GET /` (alias `/geo-zones`) | Search/filter/paginate zones. |
| `GeoZones\CreateZone` | `GET /geo-zones/create` | Draw a new zone and select its category. |
| `GeoZones\EditZone` | `GET /geo-zones/{zone}/edit` | Redraw/update an existing zone (numeric IDs only). |
| `GeoZones\ViewZone` | `GET /geo-zones/{zone}` | View map + metadata for a zone. |

Routes are defined in `routes/web.php` and use numeric constraints so pages like `/geo-zones/create` are never mistaken for show routes.

## Frontend Build

- Source files live in `resources/js` and `resources/css`.
- `resources/css/app.css` imports Bootstrap, Leaflet, and Leaflet Draw styles via `postcss-import`.
- `resources/js/app.js` registers Leaflet globally, wires in Leaflet Draw, and loads marker assets so Leaflet icons work after bundling.
- `webpack.mix.js` copies required Leaflet/Leaflet Draw images into `public/css/images`.

Common npm scripts:

| Command | Description |
| --- | --- |
| `npm run dev` | Development build, no minification. |
| `npm run watch` | Rebuild on change (great while running `php artisan serve`). |
| `npm run build` / `npm run production` | Minified production bundle with mix-manifest hashes. |

## API

Two read-only endpoints expose the same data used by the Livewire UI:

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/geo-zones` | Paginated list with optional `search`, `category_id`, `per_page` query params. |
| `GET` | `/api/geo-zones/{id}` | Single zone (includes category + GeoJSON geometry). |

Responses use `CategoryResource` and `GeoZoneResource`, so the payload is stable for other clients.

## Testing

Make sure `.env.testing` points to a PostGIS-enabled database (run `php artisan db:create --env=testing` once, then `php artisan migrate --env=testing`).

Run the suite with:
```bash
php artisan test
```
Add feature/unit tests under `tests/` as you extend the system.

## Troubleshooting

| Issue | Fix |
| --- | --- |
| `SQLSTATE[42P07]: relation "geo_zones" already exists` | Run `php artisan migrate:fresh --seed` after confirming you can drop the existing tables. |
| `SQLSTATE[42P01]: relation "categories" does not exist` during migrations | Ensure the categories migration runs before the geo_zones migration (already ordered via filenames). |
| Leaflet map renders without tiles/icons | Make sure `npm run dev` has been executed and the assets in `public/` are up to date. |
| `/geo-zones/create` returns 404 | Confirm the dev server restarted after pulling routes; numeric constraints can cause cached routes to misbehave until `php artisan route:clear` is run. |
