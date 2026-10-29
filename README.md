# 🎵 MyMovieApp + OPM Playlist Manager

An all-in-one web application uniting **MyMovieApp** and **OPM Playlist Manager**, built with a **Laravel PHP REST API (MySQL)** backend and a modern **Quasar Framework (Vue 3 + TypeScript)** frontend.

---

## 🌟 Key Features & Permissions

| Feature | Description | Permission Level |
| :--- | :--- | :--- |
| **🎵 OPM Music Catalog** | Browse, search, and discover classic and contemporary Filipino hit songs (*Up Dharma Down*, *Ben&Ben*, *Eraserheads*, *Juan Karlos*, *Rivermaya*, etc.). | **Public** |
| **🎧 Interactive Audio Player** | Floating bottom player with HTML5 audio playback, progress seek bar, volume slider, track prev/next, shuffle, and repeat controls. | **Public** |
| **🔍 Search & Genre Filter** | Real-time client and server filtering by song title, artist name, and OPM genres (*OPM Rock*, *OPM Pop*, *OPM Acoustic*). | **Public** |
| **🎬 Movie Hub Integration** | Discover featured Filipino movie soundtracks with embedded YouTube video previews. | **Public** |
| **♥ Favorites & Playlist Manager** | One-click toggles for adding songs and movie soundtracks to personal favorites and playlists with instant MySQL persistence. | **Authenticated (Listeners & Admins)** |
| **📊 Live Personal Dashboard** | View personal listening statistics (favorite songs, playlist items, favorite movies) and recent tracks. | **Authenticated (Listeners & Admins)** |
| **➕ Add & Manage OPM Songs** | Modal dialog for creating new tracks and saving them directly into the database, with edit and delete controls. | **Admin Only** |
| **🎬 Manage Movie Soundtracks** | Create, edit, and remove movie soundtracks with YouTube URL parsing. | **Admin Only** |
| **👥 User Account Management** | View registered users, update roles (`user` / `admin`), and manage access safely with demotion safeguards. | **Admin Only** |
| **📈 Live Admin Statistics** | System-wide statistics covering accounts, catalog volume, and aggregate listener engagement. | **Admin Only** |
| **🎨 Modern Glassmorphic UI** | Ultra-sleek dark styling built with custom SCSS design tokens, responsive drawer, and notification alerts. | **All Users** |

> [!NOTE]
> **Permission Model**: Public visitors can browse the catalog and stream previews. Authenticated users (Listeners) enjoy their own favorites and playlists. Administrators manage the catalog and user accounts. Normal listeners cannot add, edit, or delete catalog records.

---

## 🏗️ Tech Stack & Architecture

### Backend (`/backend`)
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: MySQL (`mymovieapp`)
- **Authentication**: Laravel Sanctum (API Bearer Tokens)
- **Authorization**: Laravel Gates (`admin` gate) & Sanctum Middleware
- **API Architecture**: RESTful JSON API
- **Key Files**:
  - Models: `app/Models/Song.php`, `app/Models/Movie.php`, `app/Models/User.php`
  - Controllers: `app/Http/Controllers/SongController.php`, `app/Http/Controllers/MovieController.php`, `app/Http/Controllers/AuthController.php`, `app/Http/Controllers/AdminUserController.php`
  - Seeders: `database/seeders/UserSeeder.php`, `database/seeders/SongSeeder.php`, `database/seeders/MovieSeeder.php`
  - Routes: `routes/api.php`

### Frontend (`/frontend`)
- **Framework**: Quasar Framework v2 (Vue 3 + TypeScript)
- **State Management**: Pinia Store (`src/stores/auth.ts`)
- **Routing**: Vue Router (Hash mode)
- **HTTP Client**: Axios with API Proxying (`/api` -> `http://127.0.0.1:8000`)
- **Styling**: Vanilla SCSS + Quasar Components + Glassmorphism UI
- **Key Files**:
  - Main Dashboard & Catalog: `src/pages/IndexPage.vue`
  - User Dashboard: `src/pages/DashboardPage.vue`
  - Song Card & Track Row: `src/components/SongCard.vue`, `src/components/TrackRow.vue`
  - Audio Player: `src/components/AudioPlayerBar.vue`
  - Movie Hub: `src/components/MovieHub.vue`
  - Admin Suite: `src/pages/admin/` (`AdminDashboardPage.vue`, `AdminSongsPage.vue`, `AdminMoviesPage.vue`, `AdminUsersPage.vue`, `AdminStatisticsPage.vue`)
  - Layouts: `src/layouts/MainLayout.vue`, `src/layouts/AdminLayout.vue`

---

## 🛠️ Installation & Setup Instructions

### Prerequisites
- PHP >= 8.2 & Composer
- Node.js >= 18.x & npm
- MySQL Server running on `127.0.0.1:3306`

---

### 1. Backend Setup (Laravel API)

```bash
# 1. Navigate to backend directory
cd backend

# 2. Install PHP dependencies
composer install

# 3. Configure environment variables in .env
# Ensure database and administrator settings are configured:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mymovieapp
DB_USERNAME=root
DB_PASSWORD=

# Initial Administrator Configuration (used by UserSeeder)
ADMIN_NAME="System Administrator"
ADMIN_EMAIL=admin@mymovieapp.com
ADMIN_PASSWORD=admin12345

# 4. Run database migrations & seed initial administrator, songs, and movies
php artisan migrate --seed

# 5. Start the Laravel development backend server
php artisan serve
```
*The backend API will run at `http://127.0.0.1:8000`.*

> [!TIP]
> **Initial Administrator Account**: The `UserSeeder` checks for the configured `ADMIN_EMAIL`. If no account exists with that email, it safely provisions an administrator with the hashed password. If the account already exists, its password is preserved to prevent accidental resets.

---

### 2. Frontend Setup (Quasar Vue App)

```bash
# 1. Navigate to frontend directory
cd frontend

# 2. Install Node dependencies
npm install

# 3. Check TypeScript compilation (optional)
npm run typecheck

# 4. Verify code formatting and linting (optional)
npm run lint:check

# 5. Start the Quasar development server
npm run dev
```
*The frontend web app will run at `http://localhost:9000` (or Vite dev URL).*

---

## 📡 REST API Documentation

| Method | Endpoint | Description | Access / Permission |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/hello` | Backend health check & API status | Public |
| `POST` | `/api/auth/register` | Register a new listener account (always assigned `user` role) | Public (Throttled) |
| `POST` | `/api/auth/login` | Authenticate with email & password, returns Sanctum Bearer token | Public (Throttled) |
| `GET` | `/api/songs` | List songs (supports `search`, `genre`, `favorites_only`, `playlist_only`) | Public |
| `GET` | `/api/songs/stats` | Get dashboard statistics & available genres | Public |
| `GET` | `/api/songs/{id}` | Get single song details | Public |
| `GET` | `/api/movies` | List featured movie soundtracks (supports `favorites_only`) | Public |
| `GET` | `/api/auth/me` | Get authenticated user profile & role | Authenticated |
| `POST` | `/api/auth/logout` | Revoke current access token | Authenticated |
| `PATCH` | `/api/songs/{id}/favorite` | Toggle song favorite status for current user | Authenticated |
| `PATCH` | `/api/songs/{id}/playlist` | Toggle song playlist inclusion for current user | Authenticated |
| `PATCH` | `/api/movies/{id}/favorite` | Toggle movie soundtrack favorite status for current user | Authenticated |
| `POST` | `/api/songs` | Create a new OPM song entry | **Admin Only** |
| `PUT` | `/api/songs/{id}` | Update song information | **Admin Only** |
| `DELETE` | `/api/songs/{id}` | Remove a song from database | **Admin Only** |
| `POST` | `/api/movies` | Create a new movie soundtrack entry | **Admin Only** |
| `PUT` | `/api/movies/{id}` | Update movie soundtrack information | **Admin Only** |
| `DELETE` | `/api/movies/{id}` | Remove a movie soundtrack from database | **Admin Only** |
| `GET` | `/api/admin/users` | List all user accounts and roles | **Admin Only** |
| `PUT` | `/api/admin/users/{id}` | Update user name or role (`user` / `admin`) | **Admin Only** |
| `DELETE` | `/api/admin/users/{id}` | Delete a user account (cannot self-delete) | **Admin Only** |
| `GET` | `/api/admin/stats` | Global metrics (users, songs, movies, favorites, playlists) | **Admin Only** |

---

## 👥 Pinoy Music & Media Hub Team

* **METZ S. TURA**
* **ALLEN E. INOC**
* **JOHN RIGEL V. RECUERDO**
* **JOHN KEVIN JAVINEZ**
