# 🛠️ Technologies Utilisées — FinanFlow

> Application de **Gestion des Escomptes et Refinancements** développée avec le framework Laravel.

---

## 🔧 Backend

| Technologie | Version | Rôle |
|---|---|---|
| **PHP** | ^8.2 | Langage de programmation côté serveur |
| **Laravel** | ^12.0 | Framework MVC principal |
| **Laravel Sanctum** | ^4.3 | Authentification API (tokens & sessions SPA) |
| **Laravel Tinker** | ^2.10.1 | Console REPL interactive pour l'application |
| **Maatwebsite Excel** | ^3.1 | Import / Export de fichiers Excel & CSV |
| **Eloquent ORM** | (inclus dans Laravel) | Mapping objet-relationnel pour la base de données |

---

## 🗄️ Base de Données

| Technologie | Détails |
|---|---|
| **SQLite** | Base de données relationnelle légère utilisée en développement |
| **Migrations Laravel** | 7 fichiers de migration (users, cache, jobs, escomptes, refinancements, configurations, logs) |
| **Seeders** | 6 seeders pour le peuplement des données initiales |

---

## 🎨 Frontend

| Technologie | Version | Rôle |
|---|---|---|
| **Blade** | (inclus dans Laravel) | Moteur de templates côté serveur |
| **Alpine.js** | ^3.15.8 | Framework JavaScript réactif léger |
| **Alpine.js Focus** | ^3.15.8 | Plugin de gestion du focus pour Alpine.js |
| **Tailwind CSS** | ^4.0.0 | Framework CSS utilitaire pour le design |
| **Axios** | ^1.11.0 | Client HTTP pour les requêtes AJAX / API |

---

## ⚡ Build & Outils de Développement

| Technologie | Version | Rôle |
|---|---|---|
| **Vite** | ^7.0.7 | Bundler et serveur de développement rapide |
| **Laravel Vite Plugin** | ^2.0.0 | Intégration de Vite avec Laravel |
| **@tailwindcss/vite** | ^4.0.0 | Plugin Vite pour Tailwind CSS |
| **Concurrently** | ^9.0.1 | Exécution parallèle de plusieurs processus (serveur, vite, queue…) |

---

## 🧪 Tests & Qualité de Code

| Technologie | Version | Rôle |
|---|---|---|
| **PHPUnit** | ^11.5.3 | Framework de tests unitaires et fonctionnels |
| **Mockery** | ^1.6 | Bibliothèque de mocking pour les tests |
| **FakerPHP** | ^1.23 | Génération de données aléatoires réalistes (factories, seeders) |
| **Laravel Pint** | ^1.24 | Outil de formatage et linting du code PHP (basé sur PHP-CS-Fixer) |
| **Nunomaduro Collision** | ^8.6 | Affichage amélioré des erreurs dans la console |

---

## 🐳 Infrastructure & DevOps

| Technologie | Version | Rôle |
|---|---|---|
| **Laravel Sail** | ^1.41 | Environnement Docker léger pour le développement local |
| **Laravel Pail** | ^1.2.2 | Visualisation en temps réel des logs dans le terminal |
| **Artisan CLI** | (inclus dans Laravel) | Interface en ligne de commande pour les tâches d'administration |

---

## 📐 Architecture de l'Application

```
FinanFlow/
├── app/
│   ├── Http/Controllers/     # 7 contrôleurs (Auth, Dashboard, Escompte, Refinancement, Log, Configuration, State)
│   └── Models/               # 5 modèles Eloquent (User, Escompte, Refinancement, Configuration, Log)
├── resources/
│   ├── views/                # 6 templates Blade (auth, escomptes, refinancements, logs, layouts, welcome)
│   ├── css/                  # Styles Tailwind CSS
│   └── js/                   # Alpine.js & Axios
├── routes/
│   ├── web.php               # Routes web (sessions)
│   └── api.php               # Routes API (Sanctum)
├── database/
│   ├── migrations/           # 7 migrations
│   └── seeders/              # 6 seeders
└── config/                   # 10 fichiers de configuration
```

---

## 🔑 Résumé des Versions Clés

| Composant | Version |
|---|---|
| PHP | 8.2+ |
| Laravel | 12.x |
| Tailwind CSS | 4.x |
| Alpine.js | 3.x |
| Vite | 7.x |
| SQLite | Dernière |
