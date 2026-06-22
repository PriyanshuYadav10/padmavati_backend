# Padmavati Bangles — Backend API

Node.js + Express + MongoDB REST API for contact management. MVC architecture, validation & error-handling middleware, and a JWT auth layer prepared for the future (currently disabled).

## Requirements
- Node.js 18+
- MongoDB 6+ running locally (or a MongoDB Atlas URI)

## Setup
```bash
cp .env.example .env
npm install
npm run seed     # optional sample data
npm run dev      # nodemon, or: npm start
```

Server boots at `http://localhost:4000` (configurable via `PORT`).
> macOS uses port **5000** for AirPlay Receiver, so the default here is **4000**.

## Environment (`.env`)
| Var | Default | Notes |
|-----|---------|-------|
| `PORT` | `4000` | HTTP port |
| `NODE_ENV` | `development` | |
| `MONGODB_URI` | `mongodb://127.0.0.1:27017/padmavati_bangles` | |
| `CORS_ORIGIN` | `*` | comma-separated origins or `*` |
| `JWT_SECRET` / `JWT_EXPIRES_IN` | — | for future auth |
| `AUTH_ENABLED` | `false` | flip to `true` to enforce JWT |

## Project structure
```
src/
├── config/      env + db connection
├── controllers/ HTTP layer (thin)
├── services/    business logic + data access
├── models/      Mongoose schemas (Contact, Settings)
├── routes/      route definitions
├── middleware/  validate, error, auth (JWT, disabled)
├── validators/  express-validator rule sets
├── utils/       ApiError, ApiResponse, asyncHandler, seed
├── app.js       express app
└── server.js    bootstrap + graceful shutdown
```

## Endpoints

### Contacts
| Method | Path | Body / Query |
|--------|------|--------------|
| GET | `/api/contacts` | `?page=&limit=&sort=` |
| GET | `/api/contacts/search` | `?q=<text>` |
| GET | `/api/contacts/:id` | |
| POST | `/api/contacts` | `{ name*, phone*, location, city, businessType, notes }` |
| PUT | `/api/contacts/:id` | partial contact |
| DELETE | `/api/contacts/:id` | |

### Settings
| Method | Path | Body |
|--------|------|------|
| GET | `/api/settings` | — (never returns `passcodeHash`) |
| POST | `/api/settings` | `{ passcodeEnabled?, passcode?, preferences? }` |

### Response envelope
```jsonc
// success
{ "success": true, "message": "...", "data": { /* ... */ }, "meta": { /* optional */ } }
// error
{ "success": false, "message": "Validation failed", "details": [ { "field": "phone", "message": "..." } ] }
```

## Contact schema
```jsonc
{
  "id": "ObjectId",
  "name": "string (required)",
  "phone": "string (required)",
  "location": "string",
  "city": "string",
  "businessType": "string",
  "notes": "string",
  "createdAt": "ISO date",
  "updatedAt": "ISO date"
}
```

## Example
```bash
curl -X POST http://localhost:4000/api/contacts \
  -H "Content-Type: application/json" \
  -d '{"name":"Shree Bangle House","phone":"+919812345670","city":"Jaipur","businessType":"Wholesale"}'

curl "http://localhost:4000/api/contacts/search?q=jaipur"
```

## Enabling auth later
1. Set `AUTH_ENABLED=true` in `.env`.
2. Issue JWTs (a login route/controller can reuse `jsonwebtoken` + `JWT_SECRET`).
3. `protect` middleware (already on the contact routes) will then require `Authorization: Bearer <token>`.
# padmavati_backend
