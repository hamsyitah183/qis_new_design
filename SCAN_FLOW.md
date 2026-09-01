# QIS Scanner App — Scan Flow

Base URL: `https://your-host/api`
All scan endpoints require header: `Authorization: Bearer <token>` (from login).

---

## 1. Login

```
POST /api/internal/login
Content-Type: application/json

{
  "email": "scanner@qis.gov.my",
  "password": "secret123",
  "remember_me": true
}
```

Response:

```json
{
  "status": "success",
  "token": "1|abc123...",
  "expires_at": "2026-09-27T14:30:00+08:00",
  "user": {
    "uuid": "6f0c...",
    "name": "Ali Bin Ahmad",
    "email": "scanner@qis.gov.my",
    "roles": "enforcement officer"
  }
}
```

- Store `token` in secure storage (Keychain/Keystore).
- `remember_me: true` → token valid 30 days. `false`/absent → 24 hours.
- `401` on any call = token expired → clear token → go back to login.

---

## 2. Pending List (Home Screen)

```
GET /api/permits/pending
Authorization: Bearer <token>
```

Optional filter: `?application_type=Import%20Permit`

Response:

```json
{
  "status": "success",
  "counts": {
    "Import Permit": 2,
    "Inspection Certificate": 5,
    "Consignment Certificate": 0
  },
  "permits": [
    {
      "permit_number": "IPO/123",
      "order_number": "ORD-0001",
      "item_name": "Steel Coil",
      "application_type": "Import Permit"
    }
  ]
}
```

Shows issued permits not yet approved/rejected. Tap a row → scan/validate that permit.

---

## 3. Validate (Scan QR)

After camera scans QR code, extract raw payload:

- Payload starts with `QIS1:` → send as `qr_payload` (encrypted).
- Otherwise → send as `permit_number` (manual entry).

```
GET /api/permit/validate?qr_payload=<QIS1:...>
GET /api/permit/validate?permit_number=IPO/123
Authorization: Bearer <token>
```

### Valid — show Approve / Reject buttons

```json
{
  "status": "success",
  "valid": true,
  "message": "Valid",
  "permit_number": "IPO/123",
  "order_number": "ORD-0001",
  "application_type": "Import Permit",
  "item_name": "Steel Coil",
  "is_used": false
}
```

### Already used — stop, show audit info

```json
{
  "status": "success",
  "valid": false,
  "message": "QR code has already been used",
  "is_used": true,
  "action": "approved",
  "endorsed_by": "Ali Bin Ahmad",
  "endorsed_at": "01-01-2025 10:30:00 AM",
  "endorsed_location": "Port Klang Gate 3",
  "endorsed_lat": 3.0,
  "endorsed_lng": 101.4
}
```

`action`: `approved` | `rejected`.

### Invalid — stop

```json
{
  "status": "success",
  "valid": false,
  "message": "Permit Not Found",
  "is_used": false
}
```

Other `message` values: `Invalid or Tampered QR Code`.

---

## 4. Approve / Reject

Requires live GPS. QR consumed once — after this, re-scan shows "already used".

```
POST /api/qr-scan/complete-scan
Authorization: Bearer <token>
Content-Type: application/json

{
  "permit_number": "IPO/123",
  "order_number": "ORD-0001",
  "application_type": "Import Permit",
  "inspection_status": "approved",
  "used_lat": 3.043,
  "used_lng": 101.445,
  "used_location": "Port Klang Gate 3"
}
```

`inspection_status`: `approved` | `rejected`.

Success:

```json
{
  "status": "success",
  "message": "Inspection result recorded."
}
```

Errors:

| Code | Meaning |
|---|---|
| 401 | token missing/expired → re-login |
| 422 | missing fields / GPS required / bad status |
| 404 | order not found |
| 409 | QR already used |

After success → remove from pending list, refresh.

---

## 5. Scan History

Own scans:

```
GET /api/qr-scan/history?result=approved&per_page=15
Authorization: Bearer <token>
```

`result` filter: `approved` | `rejected` | `valid` | `used` | `invalid` (optional). `per_page` default `15`, max `100`.

All users (admin only):

```
GET /api/qr-scan/history/all
Authorization: Bearer <token>
```

Response (paginated under `logs.data`):

```json
{
  "status": "success",
  "logs": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "scanner": { "name": "Ali Bin Ahmad", "roles": "boundary officer" },
        "permit_number": "IPO/123",
        "order_number": "ORD-0001",
        "application_type": "Import Permit",
        "result": "approved",
        "is_valid": false,
        "used_lat": 3.043,
        "used_lng": 101.445,
        "used_location": "Port Klang Gate 3",
        "scanned_at": "29-08-2026 10:00:00 AM"
      }
    ],
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

`history` (own) → `scanner` is `null`. `history/all` → includes `scanner`.

---

## 6. Logout

```
POST /api/internal/logout
Authorization: Bearer <token>
```

```json
{
  "status": "success",
  "message": "Logged out."
}
```

Token dead immediately. Clear stored token.

---

## Result Semantics

| result | meaning | is_valid |
|---|---|---|
| `valid` | scanned, still consumable | 1 |
| `used` | scanned again after consumed | 0 |
| `invalid` | not found / tampered | 0 |
| `approved` | approved (consumed) | 0 |
| `rejected` | rejected (consumed) | 0 |

`is_valid = 1` → still scannable / no decision yet.
`is_valid = 0` → already decided (approved/rejected) or invalid.

---

## App Flow Summary

```
Login → Home (pending list) → Scan QR → Validate
    ├─ valid → Approve / Reject (with GPS) → done
    ├─ already used → show audit (who/when/where) → back
    └─ invalid → error message → back
```
