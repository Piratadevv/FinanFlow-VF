# Migration Specification — Gestion des Escomptes Bancaires

> **Purpose**: This document is an exhaustive blueprint for rebuilding the entire application in **Laravel** with **100% feature parity**. Every route, data model, validation rule, business calculation, and UI component is documented below.

---

## Table of Contents

1. [Routes](#1-routes)
2. [Database Schema](#2-database-schema)
3. [Authentication & Authorization](#3-authentication--authorization)
4. [Business Logic](#4-business-logic)
5. [External Services & Integrations](#5-external-services--integrations)
6. [File & Media Handling](#6-file--media-handling)
7. [Environment Variables](#7-environment-variables)
8. [Real-Time Features](#8-real-time-features)
9. [React Components Inventory](#9-react-components-inventory)
10. [Validation Rules](#10-validation-rules)

---

## 1. ROUTES

### 1.1 Frontend React Routes

All frontend routes are defined in `src/App.tsx`. Every route below is **protected** — the entire `<AppContent>` component is wrapped in `<ProtectedRoute>`, which renders `<LoginForm>` if the user is not authenticated.

| Path | Component | Protected | Description |
|------|-----------|-----------|-------------|
| `/` | `<Navigate to="/escomptes">` | ✅ Yes | Redirect to escomptes page |
| `/escomptes` | `<EscomptesPage>` | ✅ Yes | Main escomptes management page |
| `/refinancements` | `<RefinancementsPage>` | ✅ Yes | Refinancements management page |
| `/logs` | `<LogsPage>` | ✅ Yes | Application audit logs viewer |

**Login Page**: Not a route — `LoginForm` is rendered inline by `ProtectedRoute` when unauthenticated.

---

### 1.2 Backend API Endpoints (Express.js — `server.js`, Port 3001)

No middleware authentication is applied to any backend route — the backend is fully open. Authentication is handled entirely on the frontend via `AuthContext`.

#### Configuration Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `GET` | `/api/configuration` | Get current configuration (autorisationBancaire) | None |
| `PUT` | `/api/configuration` | Update configuration | None |
| `POST` | `/api/configuration/reset` | Reset all data to initial state (config + escomptes + refinancements) | None |
| `POST` | `/api/configuration/validate-autorisation` | Validate autorisation value (stub — always returns `{valid: true}`) | None |
| `POST` | `/api/configuration/calculate-impact` | Calculate impact of changing autorisation (stub with hardcoded values) | None |

#### Escomptes Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `GET` | `/api/escomptes` | List all escomptes with optional filtering (recherche, dateDebut, dateFin, montantMin, montantMax) | None |
| `GET` | `/api/escomptes/:id` | Get a single escompte by ID | None |
| `POST` | `/api/escomptes` | Create a new escompte | None |
| `PUT` | `/api/escomptes/:id` | Update an existing escompte | None |
| `DELETE` | `/api/escomptes/:id` | Delete an escompte | None |
| `GET` | `/api/escomptes/export` | Export escomptes data (CSV or Excel format) with optional filters | None |
| `POST` | `/api/escomptes/recalculate` | Recalculate totals (stub — returns `{success: true}`) | None |

#### Refinancements Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `GET` | `/api/refinancements` | List all refinancements (paginated response) | None |
| `GET` | `/api/refinancements/:id` | Get a single refinancement by ID | None |
| `POST` | `/api/refinancements` | Create a new refinancement (auto-calculates totalInterets) | None |
| `PUT` | `/api/refinancements/:id` | Update a refinancement (auto-recalculates interests if montant/taux/duree changed) | None |
| `DELETE` | `/api/refinancements/:id` | Delete a refinancement | None |
| `GET` | `/api/refinancements/export` | Export refinancements data (CSV or Excel format) | None |

#### Dashboard Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `GET` | `/api/dashboard/kpi` | Get all KPI metrics (cumuls, encours, pourcentages for escomptes, refinancements, and global) | None |

#### Logs Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `GET` | `/api/logs` | Get logs with pagination and filters (search, category, action, severity, entityType, dateStart, dateEnd) | None |
| `POST` | `/api/logs` | Create a new log entry | None |
| `DELETE` | `/api/logs/:id` | Delete a specific log entry | None |
| `DELETE` | `/api/logs` | Delete all logs (requires `?confirm=true` query param) | None |
| `GET` | `/api/logs/stats` | Get log statistics (counts by category, action, severity, entityType, time-based) | None |

#### State Management Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `POST` | `/api/save-state` | Save complete application state (escomptes, refinancements, configuration) | None |
| `GET` | `/api/current-state` | Get current application state with summary | None |

#### Fallback/Redirect Endpoints

| Method | Path | Description | Middleware |
|--------|------|-------------|------------|
| `GET` | `/api/undefined` | Fallback — redirects to escomptes list | None |
| `GET` | `/api/undefined/kpi` | Fallback — redirects to dashboard KPI | None |
| `GET` | `/api/undefined/export` | Fallback — simulated export | None |
| `POST` | `/api/undefined` | Fallback — returns `{message: 'Endpoint simulé'}` | None |

---

## 2. DATABASE SCHEMA

> **Current Implementation**: The application uses **in-memory JavaScript arrays** — there is **no database**. Data resets on server restart. For the Laravel migration, these should become proper database tables with Eloquent models.

### 2.1 `escomptes` Table

| Field | Type | Constraints | Default | Description |
|-------|------|-------------|---------|-------------|
| `id` | `string` (UUID recommended) | Primary Key, Required | Auto-generated (sequential string e.g. "1", "2") | Unique identifier |
| `numero_effet` | `string` | Optional | `null` | Effect number (e.g. "EFF001") |
| `nom_tireur` | `string` | Optional | `null` | Drawer name |
| `date_remise` | `string` (date `YYYY-MM-DD`) | Required, min 1 char | Current date if empty | Submission date |
| `libelle` | `string` | Required, min 1 char, max 255 chars | `"Escompte {id}"` | Label/description |
| `montant` | `decimal(12,2)` | Required, positive, max 2 decimals | `0` | Amount in DH |
| `taux_escompte` | `decimal(5,2)` | Optional | `null` | Discount rate percentage |
| `frais_commission` | `decimal(12,2)` | Optional | `null` | Commission fees |
| `montant_net` | `decimal(12,2)` | Optional | `null` | Net amount |
| `statut` | `enum('ACTIF','TERMINE','SUSPENDU')` | Optional | `'ACTIF'` | Status |
| `ordre_saisie` | `integer` | Auto-incremented | Count of existing records + 1 | Entry order |
| `date_creation` | `timestamp` | Auto-set | Current date/time | Creation timestamp |
| `date_modification` | `timestamp` | Auto-updated | Current date/time | Last modification timestamp |

**Notes on seed data**: Two initial escomptes are seeded with specific data including `numeroEffet`, `nomTireur`, `tauxEscompte`, `fraisCommission`, and `montantNet` fields.

### 2.2 `refinancements` Table

| Field | Type | Constraints | Default | Description |
|-------|------|-------------|---------|-------------|
| `id` | `string` (UUID recommended) | Primary Key, Required | Auto-generated | Unique identifier |
| `libelle` | `string` | Required, min 1 char, max 255 chars | `"Refinancement {timestamp}"` | Label/description |
| `montant_refinance` | `decimal(12,2)` | Required, positive, max 2 decimals | `0` | Refinanced amount |
| `taux_interet` | `decimal(5,2)` | Required, min 0, max 100, max 2 decimals | `0` | Interest rate (%) |
| `date_refinancement` | `string` (date `YYYY-MM-DD`) | Required, min 1 char | Current date | Refinancement date |
| `duree_en_mois` | `integer` | Required, positive, max 360 | `12` | Duration in months |
| `encours_refinance` | `decimal(12,2)` | Required, min 0, max 2 decimals | `0` | Outstanding refinanced balance |
| `frais_dossier` | `decimal(12,2)` | Optional, min 0, max 2 decimals | `0` | Processing fees |
| `conditions` | `text` | Optional, max 500 chars | `''` | Conditions text |
| `statut` | `enum('ACTIF','TERMINE','SUSPENDU')` | Required | `'ACTIF'` | Status |
| `total_interets` | `decimal(12,2)` | Auto-calculated | Computed | Total interest amount (calculated field) |
| `ordre_saisie` | `integer` | Auto-incremented | Count + 1 | Entry order |
| `date_creation` | `timestamp` | Auto-set | Current date/time | Creation timestamp |
| `date_modification` | `timestamp` | Auto-updated | Current date/time | Last modification timestamp |

**Interest calculation formula**:
```
totalInterets = montantRefinance × (tauxInteret / 100) × (dureeEnMois / 12)
```

### 2.3 `configuration` Table (Single Row)

| Field | Type | Constraints | Default | Description |
|-------|------|-------------|---------|-------------|
| `id` | `integer` | Primary Key | `1` | Always single row |
| `autorisation_bancaire` | `decimal(12,2)` | Required, positive, max 2 decimals | `200000` (initial), `100000` (after reset) | Bank authorization limit |

### 2.4 `logs` Table

| Field | Type | Constraints | Default | Description |
|-------|------|-------------|---------|-------------|
| `id` | `string` | Primary Key | `"log_{timestamp}_{random}"` | Unique identifier |
| `timestamp` | `timestamp` | Required | Current date/time | When the action occurred |
| `action` | `enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT','EXPORT','IMPORT','SAVE_STATE')` | Required | — | Action type |
| `category` | `enum('data','ui','system','configuration','error','escompte','refinancement','auth')` | Required | — | Log category |
| `severity` | `enum('LOW','MEDIUM','HIGH','CRITICAL','info','warning')` | Required | `'info'` | Severity level |
| `message` | `text` | Required (either message or description) | — | Log message |
| `description` | `text` | Alternative to message | — | Log description |
| `entity_type` | `enum('escompte','refinancement','configuration','user','export','system','ESCOMPTE','REFINANCEMENT','CONFIGURATION','USER','SYSTEM')` | Optional | `null` | Entity type involved |
| `entity_id` | `string` | Optional | `null` | ID of the entity involved |
| `user_id` | `string` | Optional | `null` | User who performed the action |
| `changes` | `json` | Optional | `null` | Before/after values `{before: {...}, after: {...}}` |
| `metadata` | `json` | Optional | `{}` | Additional context (userAgent, ip, format, etc.) |

**Note**: Logs are capped at 10,000 entries in memory. Oldest are trimmed when limit is exceeded.

### 2.5 Relationships

There are **no foreign key relationships** between tables in the current implementation. Each entity is independent. However, in the Laravel rebuild:
- Logs reference `entity_type` + `entity_id` for polymorphic association
- Configuration is a standalone singleton
- Escomptes and Refinancements share the same `autorisationBancaire` for KPI calculations

---

## 3. AUTHENTICATION & AUTHORIZATION

### 3.1 Auth System

**Type**: Frontend-only authentication using React Context + localStorage. **No backend JWT/sessions/OAuth**.

**Implementation** (`src/contexts/AuthContext.tsx`):

```typescript
// Hardcoded credentials
const VALID_USERS = [
  { username: 'USERtest', password: 'test123' },
  { username: 'abderrahmane', password: 'test123' }
];
```

**Auth Flow**:
1. User enters username/password in `LoginForm`
2. `AuthContext.login()` checks against hardcoded `VALID_USERS` array
3. On success: sets `isAuthenticated = true`, stores in `localStorage` (`isAuthenticated`, `username`)
4. On failure: returns `false`, displays error message
5. On app load: checks `localStorage` to restore previous session
6. Logout clears `localStorage` and resets state

**Protected Routes**: The entire `AppContent` is wrapped in `<ProtectedRoute>`. If `!isAuthenticated`, renders `<LoginForm>` instead of app content.

### 3.2 API Client Auth Header

The API client (`src/services/api/client.ts`) includes a Bearer token interceptor:
```typescript
const token = localStorage.getItem('authToken');
if (token) {
  config.headers.Authorization = `Bearer ${token}`;
}
```
However, **no token is ever set** — this is scaffolding for future use. The backend has **zero auth middleware**.

### 3.3 Roles/Permissions

**There are no roles or permissions**. All authenticated users have full access to all features (CRUD on all entities, export, configuration, logs).

### 3.4 Laravel Migration Recommendations

For the Laravel rebuild, implement:
- Laravel Sanctum or Passport for API token auth
- Database-backed users table with hashed passwords
- Middleware protection on all API routes
- Role/permission system if needed (all users currently have identical access)

---

## 4. BUSINESS LOGIC

### 4.1 KPI Calculation (`GET /api/dashboard/kpi`)

This is the central business logic. **Core calculations** (from `server.js` lines 353-393):

```javascript
// Escomptes KPIs
const cumulTotal = escomptesData.reduce((sum, e) => sum + Number(e.montant || 0), 0);
const nombreEscomptes = escomptesData.length;

// Refinancements KPIs
const cumulRefinancements = refinancementsData.reduce((sum, r) => sum + Number(r.montantRefinance || 0), 0);
const nombreRefinancements = refinancementsData.length;

// Global KPIs (combined)
const cumulGlobal = cumulTotal + cumulRefinancements;
const autorisation = configurationData.autorisationBancaire || 0;

// Remaining authorization
const encoursRestant = autorisation - cumulTotal;
const encoursRestantGlobal = autorisation - cumulGlobal;

// Utilization percentages
const pourcentageUtilisation = autorisation > 0
  ? Math.round((cumulTotal / autorisation) * 100) : 0;
const pourcentageUtilisationGlobal = autorisation > 0
  ? Math.round((cumulGlobal / autorisation) * 100) : 0;
```

**KPI Response Shape**:
```json
{
  "cumulTotal": 80000,
  "encoursRestant": 120000,
  "nombreEscomptes": 2,
  "pourcentageUtilisation": 40,
  "cumulRefinancements": 100000,
  "nombreRefinancements": 2,
  "cumulGlobal": 180000,
  "encoursRestantGlobal": 20000,
  "pourcentageUtilisationGlobal": 90,
  "autorisationBancaire": 200000
}
```

### 4.2 Interest Calculation (Refinancements)

**Formula** (from `server.js` line 849):
```javascript
totalInterets = montantRefinance * (tauxInteret / 100) * (dureeEnMois / 12);
```

This is a **simple annual interest** formula. On update, interests are recalculated if `montantRefinance`, `tauxInteret`, or `dureeEnMois` changed.

### 4.3 Precision Arithmetic (`src/utils/calculations.ts`)

The frontend uses integer-based arithmetic to avoid floating-point errors:

```typescript
function dirhamsVersCentimes(dirhams: number): number {
  return Math.round(dirhams * 100);
}

function centimesVersDirhams(centimes: number): number {
  return centimes / 100;
}

function calculerCumulTotal(escomptes: Escompte[]): number {
  let cumulCentimes = 0;
  for (const escompte of escomptes) {
    cumulCentimes += dirhamsVersCentimes(escompte.montant);
  }
  return centimesVersDirhams(cumulCentimes);
}
```

**Other calculation functions**:
- `calculerEncoursRestant(autorisation, cumul)` → `autorisation - cumul`
- `calculerPourcentageUtilisation(cumul, autorisation)` → `(cumul / autorisation) * 100` rounded to 2 decimals
- `verifierDepassementAutorisation(nouveauMontant, cumulActuel, autorisation)` → boolean
- `calculerImpactNouveauMontant(...)` → new cumul, new encours, new percentage, overflow flag
- `formaterMontant(montant, options)` → `"45 000,00 DH"` (fr-FR locale)
- `formaterPourcentage(percentage, decimals)` → `"40,0 %"`
- `calculerStatistiquesMontants(escomptes)` → total, average, min, max, count
- `calculerCumulRefinancements(refinancements)` → sum of montantRefinance
- `calculerCumulGlobal(cumulEscomptes, cumulRefinancements)` → combined
- `calculerEncoursRestantGlobal(autorisation, cumulGlobal)`
- `calculerPourcentageUtilisationGlobal(cumulGlobal, autorisation)`

### 4.4 Escompte Filtering (`GET /api/escomptes`)

Backend filtering logic:
```javascript
if (recherche) {
  data = data.filter(e => e.libelle.toLowerCase().includes(recherche.toLowerCase()));
}
if (dateDebut) {
  data = data.filter(e => e.dateRemise >= dateDebut); // string comparison
}
if (dateFin) {
  data = data.filter(e => e.dateRemise <= dateFin);
}
if (montantMin !== undefined) {
  data = data.filter(e => e.montant >= montantMin);
}
if (montantMax !== undefined) {
  data = data.filter(e => e.montant <= montantMax);
}
```

### 4.5 Log Filtering (`GET /api/logs`)

Backend log filtering:
- **Text search**: searches in `message`, `description`, `entityType`, `entityId`
- **Category**: exact match
- **Action**: exact match
- **Severity**: exact match
- **Entity type**: exact match
- **Date range**: `timestamp >= dateStart`, `timestamp <= dateEnd (23:59:59)`
- **Sort**: by timestamp descending (newest first)
- **Pagination**: page-based with configurable limit (default 50)

### 4.6 Log Statistics (`GET /api/logs/stats`)

Aggregates counts by category, action, severity, entityType, and counts logs from last 24 hours and last 7 days.

### 4.7 Configuration Reset (`POST /api/configuration/reset`)

Resets to:
- `autorisationBancaire`: `100000`
- Escomptes: restored to initial seed data (2 records)
- Also returns recalculated KPIs

### 4.8 Redux Logging Middleware (`src/store/middleware/loggingMiddleware.ts`)

**Critical business logic**: The middleware intercepts all Redux actions and creates audit log entries for significant actions.

**Significant actions tracked**:
- `escomptes/createEscompte/fulfilled`
- `escomptes/updateEscompte/fulfilled`
- `escomptes/deleteEscompte/fulfilled`
- `escomptes/exportEscomptes/fulfilled`
- `refinancements/createRefinancement/fulfilled`
- `refinancements/updateRefinancement/fulfilled`
- `refinancements/deleteRefinancement/fulfilled`
- `refinancements/exportRefinancements/fulfilled`
- `configuration/updateConfiguration/fulfilled`

For each significant action, it:
1. Captures the entity type, ID, and description
2. Captures before/after state changes
3. Creates a log entry with metadata (userAgent, URL)
4. Sends the log entry to the backend via `POST /api/logs`

### 4.9 Auto-Refresh

Dashboard KPIs are automatically refreshed every **30 seconds** via `setInterval` in `App.tsx`.

---

## 5. EXTERNAL SERVICES & INTEGRATIONS

### 5.1 Third-Party APIs

**There are NO external third-party APIs or services used.** The application is entirely self-contained.

### 5.2 External Assets

- **Logo**: Loaded from external URL: `https://i0.wp.com/unimagec.ma/wp-content/uploads/2021/03/Logo-Unimagec-Web3.png?w=370&ssl=1` (used in `LoginForm.tsx`)

### 5.3 Libraries Used (require equivalent in Laravel)

| Library | Purpose | Laravel Equivalent |
|---------|---------|-------------------|
| `express` 5.1.0 | HTTP server | Laravel framework (built-in) |
| `cors` | Cross-origin requests | Laravel CORS middleware |
| `xlsx` 0.18.5 | Excel file generation for exports | PhpSpreadsheet or Laravel Excel |
| `winston` + `winston-daily-rotate-file` | Structured logging with rotation | Laravel Log (Monolog) with daily channel |
| `morgan` | HTTP request logging | Laravel middleware logging |
| `zod` 3.25.76 | Schema validation | Laravel Form Requests / Validator |
| `axios` 1.6.2 | HTTP client | Not needed (backend handles directly) |
| `date-fns` 2.30.0 | Date manipulation | Carbon (built into Laravel) |
| `@reduxjs/toolkit` | State management | Livewire state or Inertia.js |
| `react-router-dom` 7.8.1 | Client routing | Laravel routes or Inertia.js |
| `tailwindcss` 3.3.5 | CSS framework | Tailwind CSS (same) |

---

## 6. FILE & MEDIA HANDLING

### 6.1 Export Functionality

**Escomptes Export** (`GET /api/escomptes/export`):
- **Formats**: CSV and Excel (XLSX)
- **CSV**: UTF-8 with BOM (`\uFEFF`), comma-separated, special chars escaped
- **Excel**: Uses `xlsx` library, sheet named `"Escomptes"`
- **Columns exported**: ID, Date de remise, Libellé, Montant, Ordre de saisie, Date de création, Date de modification
- **Filters applied**: recherche, dateDebut, dateFin, montantMin, montantMax
- **Filename pattern**: `escomptes_YYYY-MM-DD.csv` or `escomptes_YYYY-MM-DD.xlsx`

**Refinancements Export** (`GET /api/refinancements/export`):
- **Formats**: CSV and Excel (XLSX)
- **Columns exported**: ID, Libellé, Montant Refinancé, Taux Intérêt (%), Durée (mois), Date Refinancement, Encours Refinancé, Statut, Total Intérêts, Date Création
- **Filename pattern**: `refinancements_YYYY-MM-DD.csv` or `refinancements_YYYY-MM-DD.xlsx`

### 6.2 File Download Mechanism (Frontend)

The `ApiClient.downloadFile()` method:
1. Sends GET request with `responseType: 'blob'` and 30s timeout
2. Creates a temporary blob URL
3. Creates a hidden `<a>` element with `download` attribute
4. Triggers click to start download
5. Cleans up blob URL and DOM element

### 6.3 File Upload (Scaffolding Only)

`ApiClient.uploadFile()` exists but is **not used** by any feature. It supports:
- `multipart/form-data` content type
- Upload progress callback
- Single file upload via FormData

### 6.4 No Persistent Storage

All data is in-memory. No files are stored on disk except for Winston log files in the `logs/` directory.

---

## 7. ENVIRONMENT VARIABLES

### 7.1 Frontend (React)

| Variable | Used In | Default Value | Description |
|----------|---------|---------------|-------------|
| `REACT_APP_API_URL` | `src/services/api/client.ts` | `http://localhost:3001` | Backend API base URL |

### 7.2 Backend (Node.js)

| Variable | Used In | Default Value | Description |
|----------|---------|---------------|-------------|
| `LOG_LEVEL` | `server.js` (Winston config) | `'info'` | Minimum log level |
| `NODE_ENV` | `server.js` (console transport) | — | Environment mode (controls console logging) |

### 7.3 Laravel Equivalent `.env` Variables

```env
APP_NAME="Gestion des Escomptes"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_escomptes
DB_USERNAME=root
DB_PASSWORD=
LOG_CHANNEL=daily
LOG_LEVEL=info
```

---

## 8. REAL-TIME FEATURES

### 8.1 Current Implementation

**There are NO WebSocket, Socket.io, or server-push features** in the current application.

### 8.2 Polling-Based Updates

The application uses **HTTP polling** instead of real-time:
- Dashboard KPIs are refreshed every **30 seconds** via `setInterval` in `App.tsx`
- This calls `GET /api/dashboard/kpi` repeatedly

### 8.3 Auto-Save Feature (UI Scaffolding)

The `uiSlice` contains auto-save state scaffolding:
```typescript
autoSave: {
  enabled: true,
  lastSave: null,
  interval: 10000, // 10 seconds
}
```
This state is defined but **not actively used** in any component.

### 8.4 Laravel Migration Note

For the Laravel rebuild, consider:
- Laravel Echo + Pusher for real-time dashboard updates
- Or keep the polling approach with a simple `setInterval` on the frontend

---

## 9. REACT COMPONENTS INVENTORY

### 9.1 Page-Level Components

#### `EscomptesPage` (`src/components/Pages/EscomptesPage.tsx`)
- **Data fetched**: `fetchEscomptes()` (with pagination, filters, sort) + `fetchDashboardKPI()`
- **Actions**: Create, Edit, Delete, Export escomptes; Apply filters; Change sort; Navigate pages
- **Sub-components**: EscomptesDashboard, SearchFilters, EscomptesTable
- **Modals triggered**: EscompteModal (create/edit), ConfirmationModal (delete), ExportModal

#### `RefinancementsPage` (`src/components/Pages/RefinancementsPage.tsx`)
- **Data fetched**: `fetchRefinancements()` (with pagination, filters, sort) + `fetchDashboardKPI()`
- **Actions**: Create, Edit, Delete, Export refinancements; Apply filters; Pagination
- **Sub-components**: RefinancementsDashboard, RefinancementsTable
- **Modals triggered**: RefinancementModal (create/edit), ConfirmationModal (delete), RefinancementsExportModal

#### `LogsPage` (`src/components/Pages/LogsPage.tsx`)
- **Data fetched**: `fetchLogs()` (with pagination, filters)
- **Actions**: Apply filters (search, category, action, severity, entityType, dateRange); Clear filters; Paginate
- **Sub-components**: LogFilters, LogEntry

---

### 9.2 Shared/Reusable Components

#### Auth Components
| Component | File | Description |
|-----------|------|-------------|
| `LoginForm` | `Auth/LoginForm.tsx` | Full-page login with username/password, error display, loading state, demo credentials display |
| `ProtectedRoute` | `Auth/ProtectedRoute.tsx` | Wrapper that renders children if authenticated, LoginForm if not, LoadingSpinner while checking |

#### Layout Components
| Component | File | Description |
|-----------|------|-------------|
| `Header` | `Layout/Header.tsx` | Top navigation bar with app title, configuration button, user menu, logout, mobile toggle |
| `Sidebar` | `Layout/Sidebar.tsx` | Navigation sidebar with links to Escomptes, Refinancements, Logs pages; collapsible on mobile |
| `EdgeSwipeDetector` | `Layout/EdgeSwipeDetector.tsx` | Mobile touch gesture handler for sidebar open/close |

#### Dashboard Components
| Component | File | Description |
|-----------|------|-------------|
| `Dashboard` | `Dashboard/Dashboard.tsx` | Container with tab switching between escomptes and refinancements dashboards |
| `EscomptesDashboard` | `Dashboard/EscomptesDashboard.tsx` | KPI cards for escomptes: cumul total, encours restant, utilization %, count |
| `RefinancementsDashboard` | `Dashboard/RefinancementsDashboard.tsx` | KPI cards for refinancements: total refinanced, count, interest totals |
| `KPICard` | `Dashboard/KPICard.tsx` | Reusable card with icon, title, value, trend indicator |
| `StatCard` | `Dashboard/StatCard.tsx` | Statistical information card with progress indicators |

#### Escomptes Components
| Component | File | Description |
|-----------|------|-------------|
| `EscomptesTable` | `Escomptes/EscomptesTable.tsx` | Sortable data table with edit/delete actions, responsive scroll, pagination |
| `EscompteModal` | `Escomptes/EscompteModal.tsx` | Create/edit form: dateRemise, libellé, montant; Zod validation; autorisation check |
| `SearchFilters` | `Escomptes/SearchFilters.tsx` | Filter panel: text search, date range, amount range, reset, filter count badge |

#### Refinancements Components
| Component | File | Description |
|-----------|------|-------------|
| `RefinancementsTable` | `Refinancements/RefinancementsTable.tsx` | Sortable table with status color coding (ACTIF/TERMINÉ/SUSPENDU), interest display |
| `RefinancementModal` | `Refinancements/RefinancementModal.tsx` | Full form: libellé, montant, taux, date, durée, encours, frais, conditions, statut; auto-calculates interests |

#### Log Components
| Component | File | Description |
|-----------|------|-------------|
| `LogEntry` | `Logs/LogEntry.tsx` | Individual log display with severity color badges, expandable details, change tracking |
| `LogFilters` | `Logs/LogFilters.tsx` | Filter panel: search, category, action, severity, entityType, date range, clear all |

#### UI Components
| Component | File | Description |
|-----------|------|-------------|
| `Modal` | `UI/Modal.tsx` | Base modal: overlay, escape-to-close, backdrop click, header/content/footer areas, portal |
| `ModalContainer` | `UI/ModalContainer.tsx` | Global modal router: renders correct modal type from Redux state |
| `ConfirmationModal` | `UI/ConfirmationModal.tsx` | Confirm/cancel dialog with customizable message, warning/danger styling |
| `ExportModal` | `UI/ExportModal.tsx` | Escomptes export: format selection (CSV/Excel), date range, filter options |
| `RefinancementsExportModal` | `UI/RefinancementsExportModal.tsx` | Refinancements export: same structure as ExportModal |
| `NotificationContainer` | `UI/NotificationContainer.tsx` | Toast notification manager: success/error/warning/info, auto-dismiss, stacking |
| `LoadingOverlay` | `UI/LoadingOverlay.tsx` | Full-screen spinner overlay during async operations |
| `LoadingSpinner` | `UI/LoadingSpinner.tsx` | Reusable spinner with customizable size/color |
| `Pagination` | `UI/Pagination.tsx` | Page controls: prev/next, page numbers, items-per-page selector, total display |
| `SaveButton` | `UI/SaveButton.tsx` | Button with loading spinner, disabled state, success/error indicators |
| `ConfigurationModal` | `Configuration/ConfigurationModal.tsx` | Edit autorisation bancaire: Zod validation, real-time impact preview, confirmation |

---

## 10. VALIDATION RULES

### 10.1 Escompte Validation (Zod Schema — `src/types/index.ts`)

```typescript
EscompteSchema = z.object({
  id: z.string().uuid().optional(),
  dateRemise: z.string().min(1, 'La date de remise est obligatoire'),
  libelle: z.string()
    .min(1, 'Le libellé est obligatoire')
    .max(255, 'Le libellé ne peut pas dépasser 255 caractères'),
  montant: z.number()
    .positive('Le montant doit être positif')
    .multipleOf(0.01, 'Le montant doit avoir au maximum 2 décimales'),
  ordreSaisie: z.number().int().positive().optional(),
  dateCreation: z.string().optional(),
  dateModification: z.string().optional(),
});
```

**Additional frontend validation** (`src/utils/validation.ts`):
- `validerLibelle`: min 3 chars, max 255 chars, no `<>"'&` characters
- `validerMontantEscompte`: positive, max 2 decimals, max 999,999,999.99; checks cumul + montant ≤ autorisationBancaire; warns if >90% utilization or >10% of authorization
- `validerDateRemise`: valid date, not in future (allows today+1), warns if >1 month old, warns if weekend

### 10.2 Refinancement Validation (Zod Schema)

```typescript
RefinancementSchema = z.object({
  id: z.string().uuid().optional(),
  dateRefinancement: z.string().min(1, 'La date de refinancement est obligatoire'),
  libelle: z.string()
    .min(1, 'Le libellé est obligatoire')
    .max(255, 'Le libellé ne peut pas dépasser 255 caractères'),
  montantRefinance: z.number()
    .positive('Le montant refinancé doit être positif')
    .multipleOf(0.01, 'Le montant doit avoir au maximum 2 décimales'),
  tauxInteret: z.number()
    .min(0, 'Le taux ne peut pas être négatif')
    .max(100, 'Le taux ne peut pas dépasser 100%')
    .multipleOf(0.01, 'Le taux doit avoir au maximum 2 décimales'),
  dureeEnMois: z.number().int()
    .positive('La durée doit être positive')
    .max(360, 'La durée ne peut pas dépasser 360 mois'),
  encoursRefinance: z.number()
    .min(0, "L'encours refinancé ne peut pas être négatif")
    .multipleOf(0.01),
  fraisDossier: z.number()
    .min(0, 'Les frais de dossier ne peuvent pas être négatifs')
    .multipleOf(0.01).optional().default(0),
  conditions: z.string()
    .max(500, 'Les conditions ne peuvent pas dépasser 500 caractères').optional(),
  statut: z.enum(['ACTIF', 'TERMINE', 'SUSPENDU']).default('ACTIF'),
  ordreSaisie: z.number().int().positive().optional(),
  dateCreation: z.string().optional(),
  dateModification: z.string().optional(),
});
```

**Additional frontend validation**:
- `validerMontantRefinancement`: same rules as escompte montant (checks authorization overflow)
- `validerDateRefinancement`: same rules as escompte date

### 10.3 Configuration Validation

```typescript
ConfigurationSchema = z.object({
  autorisationBancaire: z.number()
    .positive("L'autorisation bancaire doit être positive")
    .multipleOf(0.01, "L'autorisation bancaire doit avoir au maximum 2 décimales"),
});
```

**Additional validation** (`validerAutorisationBancaire`):
- Must be positive with max 2 decimals
- Warns if < 1,000 DH (unusual)
- Warns if > 10,000,000 DH (unusual)
- Errors if less than current cumul total

### 10.4 Log Entry Validation (Backend — `POST /api/logs`)

Required fields:
- `action` — required
- `category` — required
- `message` or `description` — at least one required

### 10.5 Date Validation (`src/utils/dates.ts`)

- Must be a valid parseable date
- Cannot be more than 5 years in the future
- Cannot be more than 5 years in the past

### 10.6 Monetary Amount Validation (`src/utils/calculations.ts`)

- Must be a valid number (not NaN)
- Must be positive (>0)
- Max 2 decimal places
- Max value: 999,999,999.99 DH

### 10.7 Backend Validation (Minimal)

The backend performs **minimal validation**:
- `PUT /api/configuration`: Casts to `Number`, falls back to `100000` if NaN or negative
- `POST /api/escomptes`: Casts montant to `Number`, defaults to `0` if invalid
- `POST /api/refinancements`: Casts all numeric fields to `Number` with defaults
- `POST /api/logs`: Checks `action`, `category`, and `message/description` are present (returns 400 if missing)
- `DELETE /api/logs`: Requires `?confirm=true`

### 10.8 Laravel Migration: Validation Summary Table

| Field | Laravel Validation Rule |
|-------|------------------------|
| `escompte.dateRemise` | `required\|date\|before_or_equal:tomorrow\|after_or_equal:-5 years` |
| `escompte.libelle` | `required\|string\|min:1\|max:255` |
| `escompte.montant` | `required\|numeric\|gt:0\|regex:/^\d+(\.\d{1,2})?$/` |
| `refinancement.dateRefinancement` | `required\|date\|before_or_equal:tomorrow` |
| `refinancement.libelle` | `required\|string\|min:1\|max:255` |
| `refinancement.montantRefinance` | `required\|numeric\|gt:0\|regex:/^\d+(\.\d{1,2})?$/` |
| `refinancement.tauxInteret` | `required\|numeric\|min:0\|max:100` |
| `refinancement.dureeEnMois` | `required\|integer\|min:1\|max:360` |
| `refinancement.encoursRefinance` | `required\|numeric\|min:0` |
| `refinancement.fraisDossier` | `nullable\|numeric\|min:0` |
| `refinancement.conditions` | `nullable\|string\|max:500` |
| `refinancement.statut` | `required\|in:ACTIF,TERMINE,SUSPENDU` |
| `configuration.autorisationBancaire` | `required\|numeric\|gt:0\|regex:/^\d+(\.\d{1,2})?$/` |
| `log.action` | `required\|string` |
| `log.category` | `required\|string` |
| `log.message` | `required_without:description\|string` |

---

## Appendix A: Redux Store Structure

```
RootState
├── escomptes
│   ├── escomptes: Escompte[]
│   ├── total, page, limit, totalPages
│   ├── filters: EscompteFilters
│   ├── sort: SortOptions {field, direction}
│   ├── selectedEscompte: Escompte | null
│   ├── isLoading, error
├── refinancements
│   ├── refinancements: Refinancement[]
│   ├── total, page, limit, totalPages
│   ├── filters: RefinancementFilters
│   ├── sort: RefinancementSortOptions
│   ├── selectedRefinancement: Refinancement | null
│   ├── isLoading, error
├── configuration
│   ├── configuration: {autorisationBancaire: number} | null
│   ├── isLoading, error
├── dashboard
│   ├── kpi: DashboardKPI | null
│   ├── lastUpdated: string | null
│   ├── isLoading, error
├── logs
│   ├── logs: LogEntry[]
│   ├── filters: LogFilters
│   ├── sorting: {field, order}
│   ├── pagination: {currentPage, itemsPerPage, totalItems, totalPages}
│   ├── loading, error
├── ui
│   ├── modal: ModalState {isOpen, type, mode, data}
│   ├── notifications: Notification[]
│   ├── sidebarOpen: boolean
│   ├── theme: 'light' | 'dark'
│   ├── autoSave: {enabled, lastSave, interval}
```

---

## Appendix B: Currency & Locale

- **Currency**: Moroccan Dirham (DH)
- **Locale**: French (`fr-FR`)
- **Number format**: `45 000,00 DH` (space thousands separator, comma decimal)
- **Date format**: `dd/MM/yyyy` (e.g., `15/04/2025`)
- **Relative time**: French (`Il y a 2 heures`, `À l'instant`)

---

## Appendix C: Winston Logging Configuration

**Log files** (stored in `./logs/` directory):

| File Pattern | Level | Retention | Max Size |
|---|---|---|---|
| `error-YYYY-MM-DD.log` | Error only | 14 days | 20MB |
| `combined-YYYY-MM-DD.log` | All levels | 30 days | 20MB |
| `app-YYYY-MM-DD.log` | Info and above | 7 days | 20MB |
| `exceptions-YYYY-MM-DD.log` | Uncaught exceptions | 30 days | 20MB |
| `rejections-YYYY-MM-DD.log` | Unhandled rejections | 30 days | 20MB |

All files are **zipped** after rotation. Console logging is enabled in non-production mode.

---

## Appendix D: Seed Data

### Initial Escomptes (2 records)

| Field | Record 1 | Record 2 |
|-------|----------|----------|
| id | `'1'` | `'2'` |
| numeroEffet | `'EFF001'` | `'EFF002'` |
| nomTireur | `'Société ABC'` | `'Entreprise XYZ'` |
| montant | `45000.00` | `35000.00` |
| dateEcheance | `'2025-04-15'` | `'2025-05-20'` |
| tauxEscompte | `8.5` | `7.2` |
| fraisCommission | `450.00` | `350.00` |
| montantNet | `44550.00` | `34650.00` |
| statut | `'ACTIF'` | `'ACTIF'` |

### Initial Refinancements (2 records)

| Field | Record 1 | Record 2 |
|-------|----------|----------|
| id | `'1'` | `'2'` |
| libelle | `'Refinancement Crédit Immobilier'` | `'Refinancement Crédit Auto'` |
| montantRefinance | `60000.00` | `40000.00` |
| tauxInteret | `10` | `12` |
| dateRefinancement | `'2025-03-09'` | `'2025-03-15'` |
| dureeEnMois | `12` | `24` |
| fraisDossier | `500` | `300` |
| conditions | `'Garantie hypothécaire requise'` | `'Véhicule en garantie'` |
| statut | `'ACTIF'` | `'ACTIF'` |
| totalInterets | `6000` | `9600` |

### Initial Configuration

```json
{ "autorisationBancaire": 200000 }
```

### Initial Logs (6 sample entries)

Includes CREATE, UPDATE, DELETE (escompte), CREATE (refinancement), LOGIN, and EXPORT entries with realistic timestamps, messages, and change tracking.

---

*Document generated on 2026-02-26. Covers the complete `Gestion-des-escomptes-VF` codebase for Laravel migration with 100% feature parity.*
