# FinanFlow

Application de **Gestion des Escomptes et Refinancements**, développée avec un backend robuste en Laravel (PHP) et un front-end réactif utilisant Blade, Tailwind CSS et Alpine.js.

## 🛠️ Prérequis

Avant de lancer le projet, assurez-vous d'avoir installé les outils suivants sur votre machine principale :
- **PHP** (version >= 8.2)
- **Composer** (pour gérer les dépendances PHP)
- **Node.js** & **npm** (pour installer et compiler les modules front-end)
- **Git** (pour cloner le projet)

*(En développement, la base de données configurée par défaut est **SQLite**, donc vous n'avez pas besoin d'installer MySQL ou PostgreSQL, le fichier sera généré automatiquement).*

## 🚀 Guide d'Installation

### 1. Cloner le dépôt
Récupérez le projet sur votre machine locale et accédez au dossier :
```bash
git clone https://github.com/Piratadevv/FinanFlow-vf.git
cd FinanFlow-vf
```

### 2. Installer les dépendances (Backend & Frontend)
Installez les packages PHP nécessaires via Composer, puis les packages Node.js :
```bash
composer install
npm install
```

### 3. Configurer l'environnement
Copiez le fichier de configuration exemple pour créer votre propre fichier `.env` :
- Sur Linux/Mac (ou Git Bash) :
  ```bash
  cp .env.example .env
  ```
- Sur Windows (Invite de commandes/PowerShell) :
  ```cmd
  copy .env.example .env
  ```

### 4. Générer la clé de l'application
Générez la clé de sécurité pour l'application Laravel :
```bash
php artisan key:generate
```

### 5. Préparer la base de données
Exécutez les migrations et populer la base de données avec des jeux d'essai (seeders). 
*Si Laravel vous demande s'il doit créer la base de données `database.sqlite`, répondez "Yes" (`y`).*
```bash
php artisan migrate --seed
```

---

## 💻 Comment Lancer le Projet

### Cas 1 : En phase de développement
Pour lancer l'application en développement, utilisez la commande suivante qui va démarrer simultanément le serveur Local PHP, le serveur Vite (pour rechargement à chaud), et l'écoute de la queue de jobs :

```bash
npm run dev
```

Une fois lancée, ouvrez votre navigateur et allez sur **[http://localhost:8000](http://localhost:8000)** (L'URL exacte s'affichera dans le terminal).

### Cas 2 : Pour la production (ou pour tester le rendu final)
Si vous ne voulez pas utiliser le mode "vite dev" (Live Reload) et que vous préférez compiler les fichiers statiques une bonne fois pour toutes, exécutez le build :

```bash
npm run build
```

Puis démarrez le serveur PHP localement :
```bash
php artisan serve
```

Vous pourrez alors vous connecter sur l'URL indiquée par le terminal (ex: `http://127.0.0.1:8000`).

---

## 👨‍💻 Utilisateurs par défaut (Pour se connecter)
Une fois les seeders passés, vous pourrez vous connecter avec plusieurs types de rôles avec l'email et le mot de passe basiques générés (gouvernés par les Seeders que vous avez exécutés pour l'application).

*(Vérifiez les fichiers dans `database/seeders/` ou le code dans `DatabaseSeeder.php` pour voir l'historique des mots de passe exacts mis en place pour les utilisateurs de tests).*

---

Pour toute information sur l'architecture et les versions détaillées des packages (Vite, Alpine, Tailwind, etc.), veuillez consulter le fichier `TECHNOLOGIES.md`.
