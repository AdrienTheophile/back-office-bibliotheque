# 📚 Back-Office Bibliothèque

Back-office de gestion de bibliothèque développé avec **Symfony 7.4**, incluant une API REST sécurisée par JWT et une interface d'administration EasyAdmin.

---

## Prérequis

Avant de commencer, assurez-vous d'avoir installé sur votre machine :

| Outil | Version minimale |
|---|---|
| PHP | >= 8.2 |
| MySQL / MariaDB | >= 10.8 |
| Symfony CLI | Dernière version |
| OpenSSL | Toute version récente |

> **Note :** `composer.phar` est fourni directement dans le projet, aucune installation globale de Composer n'est nécessaire.

---

## Installation

### 1. Accéder au projet

```bash
cd back-office-bibliotheque
```

### 2. Installer les dépendances PHP

```bash
php composer.phar install
```

### 3. Configurer les variables d'environnement

Adaptez les valeurs à votre environnement local (Exemple de `DATABASE_URL` dans `.env`) :

```env
DATABASE_URL="mysql://root:root@127.0.0.1:3306/biblio?serverVersion=10.8.2-MariaDB&charset=utf8mb4"
```

### 4. Importer la base de données

Un dump SQL est fourni avec le projet. Importez-le directement dans votre SGBD :

```bash
mysql -u root -p biblio < biblio.sql
```

> Aucune migration à exécuter, la base de données est déjà prête via le dump fourni.

### 5. Installer le certificat SSL local (HTTPS)

```bash
symfony local:server:ca:install
```

### 6. Générer les clés JWT

```bash
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

Lors de la génération, la **passphrase** demandée est :

```
PassPhraseDeFabrice
```

> Vérifiez que la variable `JWT_PASSPHRASE` dans votre `.env.local` correspond bien à cette valeur.

---

## Lancement

Démarrez le serveur Symfony sur le port **8008** :

```bash
symfony server:start --port=8008
```

---

## URL d'accès

| Interface | URL |
|---|---|
| Back-office (EasyAdmin) | [https://localhost:8008/admin](https://localhost:8008/admin) |
| API REST | [https://localhost:8008/api](https://localhost:8008/api) |

---

## Authentification API (JWT)

L'API est sécurisée par JWT. Pour obtenir un token :

**Endpoint :** `POST https://localhost:8008/api/login`

```json
{
  "username": "votre_email",
  "password": "votre_mot_de_passe"
}
```

Utilisez ensuite le token retourné dans le header `Authorization` de vos requêtes :

```
Authorization: Bearer <votre_token>
```

---

## 👥 Comptes de test

Le dump SQL contient des utilisateurs pré-configurés  :

| Rôle | Email | Mot de passe |
|---|---|---|
| **Administrateur** | `admin@biblio.com` | `admin` |
| **Adhérent** | `adherent@biblio.com` | `adherent` |

> **Note :** Vous pouvez également créer de nouveaux comptes comme **Bibliothécaire**,  depuis l'interface d'administration.

---

