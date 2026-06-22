# Padmavati Bangles — Backend API (PHP / MySQL port)

A 1:1 PHP + MySQL port of the original Node/Express + MongoDB API, so it runs on
standard **Apache + PHP + MySQL** shared hosting (Webuzo/cPanel). Same endpoints,
same JSON response envelope, same validation rules. **No Composer required.**

## What changed vs. the Node version
| | Node version | This PHP version |
|---|---|---|
| Runtime | Node 18 + Express | PHP 7.4+ (Apache) |
| Database | MongoDB / Mongoose | MySQL 5.7+/8 via PDO |
| Contact `id` | Mongo `ObjectId` (24-hex) | Auto-increment **integer** |
| Password hash | bcryptjs | `password_hash` (bcrypt, cost 10) |
| Auth (disabled) | `jsonwebtoken` | pure-PHP HS256 verifier |
| Routing | Express router | `.htaccess` → `index.php` front controller |

Everything else — endpoints, `{success,message,data,meta}` envelope, error
shape, pagination, search, settings passcode handling — is identical.

## Layout
```
php/
├── .htaccess              rewrite all requests to index.php
├── index.php              front controller: CORS, routes, error handling
├── .env.example           copy to .env on the server
├── config/   env.php, db.php
├── src/      Http.php (ApiException + responses), Request, Router, Validator, Auth
├── controllers/  ContactController, SettingsController
├── services/     ContactService, SettingsService  (all DB access)
├── sql/schema.sql         CREATE TABLE statements (import in phpMyAdmin)
└── seed.php               optional sample contacts
```

## Deploy on Webuzo (argusinfotech.com)
1. **Create the database**: Power Panel → *Add Database* (e.g. `argusinf_padmavati`)
   → *Add Database User* → *Add User to Database* (grant all privileges).
2. **Create the tables**: open *phpMyAdmin* → select the DB → **Import** → upload
   `sql/schema.sql` → Go.
3. **Upload the app**: put the **contents of this `php/` folder** into the
   document root of `padmavati.argusinfotech.com` (usually
   `~/public_html/padmavati` or the subdomain's docroot shown in *Subdomains*).
4. **Configure**: copy `.env.example` → `.env` in that same folder and fill in
   `DB_NAME`, `DB_USER`, `DB_PASS`. Set `CORS_ORIGIN` to your frontend domain.
5. **Test**: visit `https://padmavati.argusinfotech.com/api/health` → should
   return `{"success":true,...}`.
6. *(optional)* run `php seed.php` from the shell, then check
   `/api/contacts`. **Delete `seed.php` afterwards.**

## Endpoints (unchanged)
```
GET    /                       welcome
GET    /api/health             health check
GET    /api/contacts            ?page=&limit=&sort=
GET    /api/contacts/search     ?q=<text>
GET    /api/contacts/:id
POST   /api/contacts            { name*, phone*, location, city, businessType, notes }
PUT    /api/contacts/:id        partial contact
DELETE /api/contacts/:id
GET    /api/settings           (never returns passcodeHash)
POST   /api/settings           { passcodeEnabled?, passcode?, preferences? }
```

## Example
```bash
curl -X POST https://padmavati.argusinfotech.com/api/contacts \
  -H "Content-Type: application/json" \
  -d '{"name":"Shree Bangle House","phone":"+919812345670","city":"Jaipur","businessType":"Wholesale"}'

curl "https://padmavati.argusinfotech.com/api/contacts/search?q=jaipur"
```

## Enabling auth later
Set `AUTH_ENABLED=true` in `.env` and issue HS256 JWTs signed with `JWT_SECRET`.
The `Auth::protect()` guard (already wired onto every contact route) will then
require `Authorization: Bearer <token>`.
