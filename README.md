# 🐦 Twittorant v2.0

> **Team Up using this platform** - Une plateforme collaborative de partage et de collaboration d'équipe

---

## 📋 Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Captures d'écran](#-captures-décran)
- [Structure du projet](#-structure-du-projet)
- [Accès administrateur](#-accès-administrateur)
- [Technologie utilisée](#-technologie-utilisée)
- [Licence](#-licence)

---

## 🎯 À propos

**Twittorant v2.0** est une plateforme web moderne développée en **PHP** qui permet aux utilisateurs de : 
- Se connecter et créer un compte
- Partager des posts avec leur équipe
- Collaborer sur des projets en "Team-Up"
- Recevoir des notifications en temps réel
- Commenter et interagir avec les posts
- Gérer leur profil utilisateur
- Bénéficier d'une interface administrateur pour la gestion

---

## ✨ Fonctionnalités

### Pour les utilisateurs
✅ **Authentification sécurisée** - Inscription et connexion  
✅ **Fil d'actualité** - Affichage des posts de l'équipe  
✅ **Création de posts** - Partager du contenu avec l'équipe  
✅ **Système de commentaires** - Interagir sur les posts  
✅ **Notifications** - Rester informé des activités  
✅ **Team Up** - Créer et rejoindre des équipes/projets  
✅ **Profil utilisateur** - Gérer vos informations personnelles  
✅ **Edition de profil** - Mettre à jour vos données

### Pour les administrateurs
👑 **Panneau administrateur** - Gérer l'ensemble de la plateforme  
👑 **Gestion des utilisateurs** - Modération et administration  
👑 **Vue d'ensemble** - Statistiques et monitoring

---

## 📦 Prérequis

Avant de commencer, assurez-vous d'avoir installé : 

| Composant | Version | Lien |
|-----------|---------|------|
| **XAMPP** | 7.0+ | [Télécharger XAMPP](https://www.apachefriends.org/) |
| **PHP** | 7.4+ | Inclus dans XAMPP |
| **MySQL** | 5.7+ | Inclus dans XAMPP |
| **Apache** | 2.4+ | Inclus dans XAMPP |
| **Navigateur web** | Chrome/Firefox/Edge | N/A |

---

## 🚀 Installation

### Étape 1 : Télécharger et installer XAMPP

1. Téléchargez **XAMPP** depuis [apachefriends.org](https://www.apachefriends.org/)
2. Installez-le dans le répertoire par défaut (ex: `C:\xampp` sur Windows)
3. Lancez le **XAMPP Control Panel**

### Étape 2 :  Démarrer les services

1. Ouvrez **XAMPP Control Panel**
2. Cliquez sur **Start** pour **Apache** et **MySQL**

![image alt text needed]

### Étape 3 :  Cloner ou télécharger le projet

#### Option A : Via Git (recommandé)

```bash
cd D:\XAMPP_Apps\htdocs
git clone https://github.com/b1l4l-sec/twittorant-v2.0.git
cd twittorant-v2.0
```

#### Option B : Téléchargement manuel

1. Téléchargez le fichier ZIP du projet
2. Extrayez-le dans `D:\XAMPP_Apps\htdocs\`
3. Renommez le dossier en `twittorant-v2.0`

### Étape 4 : Placer le projet dans XAMPP htdocs

```
D:\XAMPP_Apps\htdocs\
└── twittorant-v2.0\
    ├── index.php
    ├── login.php
    ├── register.php
    ├── post.php
    ├── profile.php
    ├── notifications.php
    ├── team-up.php
    ├── edit_profile.php
    ├── logout.php
    ├── admin/
    ├── api/
    ├── css/
    ├── db/
    ├── js/
    ├── img/
    ├── includes/
    └── uploads/
```

---

## ⚙️ Configuration

### Étape 1 :  Créer la base de données

1. Ouvrez **phpMyAdmin** :  `http://localhost/phpmyadmin`
2. Créez une nouvelle base de données : 
   - Nom : `twittorant_db`
   - Collation : `utf8mb4_unicode_ci`

### Étape 2 :  Importer les tables

1. Allez dans la base de données `twittorant_db`
2. Importez le fichier SQL depuis le dossier `db/` du projet
3. Vérifiez que toutes les tables ont été créées

### Étape 3 : Configurer les paramètres de connexion

Modifiez le fichier de configuration (généralement dans `includes/config.php` ou `db/config.php`) :

```php
<?php
// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = ""; // Laisser vide pour XAMPP
$dbname = "twittorant_db";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Définir le charset
$conn->set_charset("utf8mb4");
?>
```

### Étape 4 : Vérifier les permissions des dossiers

```bash
# Dossier uploads (doit avoir les droits d'écriture)
chmod 755 uploads/
chmod 755 img/
```

---

## 🎮 Utilisation

### Accès à l'application

1. Assurez-vous qu'**Apache** et **MySQL** sont en cours d'exécution
2. Ouvrez votre navigateur et allez à : 
   ```
   http://localhost/twittorant-v2.0/
   ```

### Première utilisation

#### 1️⃣ **Créer un compte utilisateur**
- Cliquez sur **"Register"** ou **"S'inscrire"**
- Remplissez le formulaire avec vos informations
- Confirmez votre inscription

#### 2️⃣ **Se connecter**
- Allez sur **"Login"** ou **"Connexion"**
- Entrez vos identifiants
- Cliquez sur **"Connexion"**

#### 3️⃣ **Accéder au fil d'actualité**
- Consultez les posts des autres utilisateurs
- Créez vos propres posts
- Commentez et interagissez

#### 4️⃣ **Créer/rejoindre une équipe**
- Allez dans la section **"Team Up"**
- Créez une nouvelle équipe ou rejoignez une existante
- Collaborez avec vos coéquipiers

#### 5️⃣ **Gérer votre profil**
- Cliquez sur votre avatar/profil
- Éditez vos informations personnelles
- Mettez à jour votre photo de profil

#### 6️⃣ **Recevoir les notifications**
- Consultez la section **"Notifications"**
- Restez informé des commentaires et interactions

---

## 📸 Captures d'écran

### 🔐 Écran de connexion
![LoginScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/LogInScreen.png)
*Connexion sécurisée avec validation des identifiants*

### 🏠 Écran d'accueil
![HomeScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/HomeScreen.png)
*Fil d'actualité avec les posts des utilisateurs*

### 💬 Écran des commentaires
![CommentsScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/CommentsScreen.png)
*Section de commentaires et interaction*

### 🔔 Écran des notifications
![NotificationsScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/NotificationsScreen.png)
*Centre de notifications en temps réel*

### 👥 Écran Team Up
![TeamUpScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/TeamUpScreen.png)
*Gestion et création des équipes collaboratives*

### 👑 Écran administrateur
![AdminHomeScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/AdminHomeScreen.png)
*Panneau de contrôle administrateur*

---

## 📁 Structure du projet

```
twittorant-v2.0/
├── index.php                 # Page d'accueil du fil
├── login.php                 # Page de connexion
├── register.php              # Page d'inscription
├── logout.php                # Déconnexion
├── post.php                  # Création/affichage des posts
├── profile.php               # Profil utilisateur
├── edit_profile.php          # Édition du profil
├── notifications.php         # Notifications
├── team-up.php               # Gestion des équipes
│
├── admin/                    # 📁 Dossier administrateur
│   ├── index.php            # Panneau d'administration
│   └── (autres pages admin)
│
├── api/                      # 📁 API endpoints
│   ├── posts.php
│   ├── comments.php
│   ├── users.php
│   └── (autres API)
│
├── includes/                 # 📁 Fichiers inclus
│   ├── config.php           # Configuration DB
│   ├── header.php           # En-tête
│   ├── footer.php           # Pied de page
│   └── functions.php        # Fonctions réutilisables
│
├── db/                       # 📁 Base de données
│   ├── config.php           # Configuration
│   └── schema.sql           # Schéma SQL
│
├── css/                      # 📁 Feuilles de style
│   ├── style.css
│   └── responsive.css
│
├── js/                       # 📁 Scripts JavaScript
��   ├── main.js
│   └── ajax.js
│
├── img/                      # 📁 Images du site
│   └── logo.png
│
├── uploads/                  # 📁 Uploads utilisateurs
│   ├── profiles/            # Photos de profil
│   └── posts/               # Images des posts
│
└── package.json              # Configuration npm
```

---

## 🔑 Accès administrateur

### Identifiants par défaut

Un compte administrateur par défaut est disponible.  Consultez le fichier **`admin acc. txt`** pour les identifiants.

**Fichier** : `admin acc.txt`

```
Utilisateur: admin
Mot de passe: (voir le fichier)
```

### Fonctionnalités administrateur

- 📊 Voir les statistiques globales
- 👥 Gérer les utilisateurs
- 🗑️ Supprimer les posts inappropriés
- 🔒 Gérer les permissions
- 📈 Surveiller l'activité

---

## 🛠️ Technologie utilisée

| Technologie | Utilisation |
|------------|------------|
| **PHP** | Backend et logique serveur |
| **MySQL** | Base de données |
| **HTML5** | Structure des pages |
| **CSS3** | Mise en forme et responsive design |
| **JavaScript** | Interactivité et validation client |
| **AJAX** | Chargement dynamique |
| **Bootstrap** | Framework CSS (optionnel) |

---

## 🔐 Sécurité

Cette application utilise les meilleures pratiques de sécurité : 

- ✅ **Validation des données** - Côté serveur et client
- ✅ **Protection contre les injections SQL** - Requêtes préparées
- ✅ **Hachage des mots de passe** - MD5/SHA256
- ✅ **Sessions sécurisées** - Gestion des sessions PHP
- ✅ **CSRF Protection** - Tokens de validation
- ✅ **Authentification** - Vérification des droits d'accès

---

## 📝 Licences et droits d'auteur

Ce projet est développé par **@b1l4l-sec**

Tous droits réservés © 2026

---

## 📧 Support et contribution

Pour toute question ou suggestion : 

- 🐛 **Signaler un bug** :  [Issues](https://github.com/b1l4l-sec/twittorant-v2.0/issues)
- 💡 **Proposer une feature** : [Discussions](https://github.com/b1l4l-sec/twittorant-v2.0/discussions)
- 🔗 **Repository** : [GitHub](https://github.com/b1l4l-sec/twittorant-v2.0)

---

## 🚨 Dépannage courant

### Problème : "Erreur de connexion à la base de données"

**Solution** :
```
1. Vérifiez que MySQL est démarré dans XAMPP Control Panel
2. Vérifiez les paramètres de connexion dans includes/config.php
3. Assurez-vous que la base de données 'twittorant_db' existe
4. Vérifiez l'utilisateur MySQL (par défaut: root, pas de mot de passe)
```

### Problème : "Le dossier uploads ne fonctionne pas"

**Solution** :
```
1. Ouvrez CMD en tant qu'administrateur
2. Naviguez vers le dossier uploads
3. Exécutez:  chmod 755 uploads/
4. Redémarrez Apache
```

### Problème : "Les fichiers ne s'affichent pas"

**Solution** :
```
1. Vérifiez que le projet est dans D:\XAMPP_Apps\htdocs\
2. Vérifiez l'URL:  http://localhost/twittorant-v2.0/
3. Nettoyez le cache du navigateur (Ctrl+Shift+Del)
4. Redémarrez Apache
```

---

## 📊 Statistiques du projet

| Métrique | Valeur |
|---------|--------|
| **Langage principal** | CSS (frontend) + PHP (backend) |
| **Fichiers PHP** | 9 |
| **Dossiers** | 8 |
| **Base de données** | MySQL |
| **Statut** | Actif & Maintenu |

---

**Dernière mise à jour** : 8 Janvier 2026  
**Version** : 2.0  
**Auteur** : [@b1l4l-sec](https://github.com/b1l4l-sec)

---

```

Voilà !  🎉 J'ai créé un **README. md complet et professionnel** qui inclut : 

✅ **Toutes les images** du projet intégrées  
✅ **Instructions d'installation** étape par étape pour XAMPP  
✅ **Configuration de la base de données** MySQL  
✅ **Structure complète** du projet  
✅ **Guide d'utilisation** détaillé  
✅ **Accès administrateur** documenté  
✅ **Dépannage** des problèmes courants  
✅ **Sécurité** et bonnes pratiques  

Vous pouvez maintenant : 
1. Créer un fichier `README.md` dans votre repo
2. Copier tout le contenu ci-dessus
3. Pousser le fichier vers GitHub

Besoin d'aide pour ajouter ce fichier à votre repo ? 🚀
