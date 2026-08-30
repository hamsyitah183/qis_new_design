# QIS API Reference

Base URL: `https://your-host/api`

All responses are JSON. Scanner endpoints marked **🔒 Auth required** need `Authorization: Bearer <token>` header (token from `POST /api/internal/login`).

---

## 1. POST `/api/internal/login`

Authenticate an internal user for the scanner app. Returns a Sanctum Bearer token.

**Request body (JSON):**

| Field | Type | Required | Rules |
|---|---|---|---|
| `email` | string | yes | valid email |
| `password` | string | yes | string |
| `remember_me` | boolean | no | `true` → token valid 30 days; absent/`false` → 24 hours |

**Example payload:**

```json
{
  "email": "scanner@qis.gov.my",
  "password": "secret123",
  "remember_me": true
}
```

**Response `200` (success):**

```json
{
  "status": "success",
  "message": "Login successful.",
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

`token` is a Sanctum plain-text token: store it securely (Keychain/Keystore), send as `Authorization: Bearer <token>`. `user.uuid` is the `scanner_user_uuid` value. Token expires after 24h (or 30d with `remember_me`), then API returns `401` and app must re-login.

**Response `422` (bad credentials):**

```json
{
  "status": "error",
  "message": "Invalid credentials."
}
```

**Response `422` (validation):**

```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## 1b. POST `/api/internal/logout` 🔒 Auth required

Revoke the current token (kills it immediately, even before expiry).

**Headers:** `Authorization: Bearer <token>`

**Response `200`:**

```json
{
  "status": "success",
  "message": "Logged out."
}
```

---

## 2. GET `/api/user`

Return the currently authenticated user. Requires `auth:sanctum` (Bearer token).

**Headers:**

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |

**Response `200`:** The authenticated `PublicUser` / `InternalUser` as JSON, without `position`:

```json
{
  "uuid": "6f0c...",
  "name": "Ali Bin Ahmad",
  "email": "scanner@qis.gov.my",
  "roles": "enforcement officer"
}
```

`roles` present for internal users; `null` for public users.

**Response `401`:** `{"message": "Unauthenticated."}`

---

## 3. GET `/api/states`

List all states, each with district count.

**Query params:** none

**Response `200`:**

```json
[
  {
    "id": 1,
    "name": "Johor",
    "districts_count": 10,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
]
```

Sorted by `name` ascending.

---

## 4. GET `/api/districts/{stateId}`

List districts belonging to a state.

**Path params:**

| Param | Type | Description |
|---|---|---|
| `stateId` | int | State ID |

**Response `200`:**

```json
[
  {
    "id": 1,
    "name": "Johor Bahru",
    "state_id": 1,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
]
```

---

## 5. GET `/api/all-districts`

List all districts, each with its state relation.

**Response `200`:**

```json
[
  {
    "id": 1,
    "name": "Johor Bahru",
    "state_id": 1,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "state": {
      "id": 1,
      "name": "Johor",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  }
]
```

---

## 6. POST `/api/districts`

Create a district. Admin endpoint.

**Request body (JSON):**

| Field | Type | Required | Rules |
|---|---|---|---|
| `name` | string | yes | max 255 |
| `state_id` | int | yes | must exist in `states.id` |

**Example payload:**

```json
{
  "name": "Muar",
  "state_id": 1
}
```

**Response `200`:**

```json
{
  "success": true,
  "message": "District created successfully",
  "data": {
    "id": 12,
    "name": "Muar",
    "state_id": 1,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

**Response `422`:** validation errors:

```json
{
  "message": "The name field is required. (and 1 more error)",
  "errors": {
    "name": ["The name field is required."],
    "state_id": ["The selected state id is invalid."]
  }
}
```

---

## 7. DELETE `/api/districts/{districtId}`

Delete a district.

**Path params:**

| Param | Type | Description |
|---|---|---|
| `districtId` | int | District ID |

**Response `200`:**

```json
{
  "success": true,
  "message": "District deleted successfully"
}
```

**Response `404`:** `ModelNotFoundException` JSON — district not found.

---

## 8. DELETE `/api/states/{stateId}`

Delete a state and all its districts.

**Path params:**

| Param | Type | Description |
|---|---|---|
| `stateId` | int | State ID |

**Response `200`:**

```json
{
  "success": true,
  "message": "State and all its districts deleted successfully"
}
```

**Response `404`:** state not found.

---

## 9. GET `/api/permit/validate` 🔒 Auth required

Validate a permit by number or by encrypted QR payload. Used by the scanner app.

**Headers:** `Authorization: Bearer <token>`. Scanner identity comes from the token; `scanner_user_type` / `scanner_user_uuid` params no longer needed.

**Query params:**

| Param | Type | Required | Description |
|---|---|---|---|
| `permit_number` | string | yes* | Permit number, e.g. `IPO/123` or `IPO123` |
| `qr_payload` | string | yes* | Encrypted `QIS1:` payload (see QR Payload Format below). Overrides `permit_number` |

\* At least one of `permit_number` / `qr_payload` required.

**Scan logging:** every call writes a `qr_scan_logs` row — `result` is `valid`, `used`, or `invalid`.

**Examples:**

```
GET /api/permit/validate?permit_number=IPO/123
GET /api/permit/validate?qr_payload=QIS1:eyJpdiI6...
```

**Response `401`:** `{"message": "Unauthenticated."}`

**Response `200` valid:**

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

**Response `200` invalid / not found / no scanner identity:**

```json
{
  "status": "success",
  "valid": false,
  "message": "Permit Not Found",
  "permit_number": "-",
  "item_name": "-",
  "is_used": false
}
```

Other possible `message` values: `Invalid or Tampered QR Code`, `Internal scanner identity is required.`

**Response `200` already used (one-time enforced):**

```json
{
  "status": "success",
  "valid": false,
  "message": "QR code has already been used",
  "permit_number": "IPO/123",
  "order_number": "ORD-0001",
  "application_type": "Import Permit",
  "item_name": "Steel Coil",
  "is_used": true,
  "action": "approved",
  "endorsed_by": "Ali Bin Ahmad",
  "endorsed_at": "01-01-2025 10:30:00 AM",
  "endorsed_location": "Port Klang Gate 3",
  "endorsed_lat": 3.0,
  "endorsed_lng": 101.4
}
```

`action` values: `approved` | `rejected` (from `qr_permit_usages.status`). `is_valid` on the logged scan = `0` when already consumed, `1` when still valid/unconsumed.

`application_type` values: `Import Permit` | `Inspection Certificate` | `Consignment Certificate`.

Permit lookup tables searched in order: `IpConsignmentPermit`, `InspectionItem`, `ConsignmentPermit`. Format is normalized (uppercase, spaces removed, `/` ignored).

---

## 10. GET `/api/permits/pending` 🔒 Auth required

List issued permits (status `paid`) not yet endorsed/ignored, grouped by application type. For scanner pending screen.

**Headers:** `Authorization: Bearer <token>`.

**Query params:**

| Param | Type | Required | Description |
|---|---|---|---|
| `application_type` | string | no | Filter to one type; empty = all |

**Example:**

```
GET /api/permits/pending
GET /api/permits/pending?application_type=Import%20Permit
```

**Response `200`:**

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

**Response `401`:** `{"message": "Unauthenticated."}`

---

## 10b. POST `/api/permits/search` 🔒 Auth required

Search issued permits (`status=paid`) by permit number and/or importer/exporter name. Searches all three permit types; one result per item (multi-item permits repeat `permit_details`).

**Headers:** `Authorization: Bearer <token>`.

**Request body (JSON):**

| Field | Type | Required | Rules |
|---|---|---|---|
| `permit_number` | string | no* | Exact match, case-insensitive |
| `importer` | string | no* | Exact match; OR with `exporter` |
| `exporter` | string | no* | Exact match; OR with `importer` |
| `limit` | int | no | Max results (default `50`, max `100`) |

\* At least one of `permit_number` / `importer` / `exporter` required.

**Matching logic:** provided fields combine as AND; importer + exporter are OR within the name group.

**Example payload:**

```json
{
  "permit_number": "SP/2608307190",
  "importer": "Chong"
}
```

**Response `200`:**

```json
{
  "status": "success",
  "permits": [
    {
      "permit_details": {
        "permit_number": "SP/2608307190",
        "application_type": "Inspection Certificate",
        "importer": "Chong",
        "exporter": "China Enterprise",
        "entrypoint": {
          "entry_name": "Sepanggar Container Port",
          "transport_type": "Sea",
          "eta": "03-09-2026"
        }
      },
      "item_details": {
        "item_name": "Apple",
        "category": null,
        "quantity": "250",
        "unit_measurement": "KG",
        "value": "25000",
        "purpose": "Commercial (Human consumption)",
        "uses": "fresh produce"
      }
    }
  ]
}
```

- `permit_details`: permit number, application type, importer, exporter, entrypoint (`entry_name`, `transport_type`, `eta` as `d-m-Y`).
- `item_details`: from `consignment_detail` JSON — `item_name`, `category`, `quantity`, `unit_measurement` (key `measure`), `value`, `purpose`, `uses`.
- `quantity` / `value` are strings (JSON source). `category` / `uses` / `purpose` null when absent.
- Multi-item permit → same `permit_details`, different `item_details` per row.
- No match → `"permits": []`.

**Error responses:**

| Code | Body |
|---|---|
| `401` | `{"message": "Unauthenticated."}` |
| `422` | `{"status":"error","message":"At least one of permit_number, importer, or exporter is required."}` |
| `403` | `{"status":"error","message":"Internal scanner identity is required."}` |

---

## 11. POST `/api/qr-scan/complete-scan` 🔒 Auth required

Record the result of a scanned permit inspection (approve/reject), with live GPS. Consumes the QR one-time.

**Headers:** `Authorization: Bearer <token>` (token from `/api/internal/login`). Scanner identity comes from the token — `scanner_user_type` / `scanner_user_uuid` query params no longer needed.

**Request body (JSON):**

| Field | Type | Required | Rules |
|---|---|---|---|
| `permit_number` | string | yes | Permit number |
| `order_number` | string | yes | Order number |
| `application_type` | string | yes | `Import Permit` / `Inspection Certificate` / `Consignment Certificate` |
| `inspection_status` | string | yes | `approved` or `rejected` |
| `used_lat` | number | yes* | Live GPS latitude |
| `used_lng` | number | yes* | Live GPS longitude |
| `used_location` | string | no | Location description |

\* Required when one-time QR enforcement is on (default on).

**Example payload:**

```json
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

**Response `200`:**

```json
{
  "status": "success",
  "message": "Inspection result recorded."
}
```

**Error responses:**

| Code | Body |
|---|---|
| `401` | `{"message": "Unauthenticated."}` (missing/expired token) |
| `422` | `{"status":"error","message":"permit_number, order_number, and application_type are required."}` |
| `422` | `{"status":"error","message":"inspection_status must be either \"approved\" or \"rejected\"."}` |
| `422` | `{"status":"error","message":"A live GPS location is required to record this scan."}` |
| `404` | `{"status":"error","message":"Order not found."}` |
| `409` | `{"status":"error","message":"QR code has already been used."}` |
| `500` | `{"status":"error","message":"Failed to log scan completion."}` |

Behavior:
- `inspection_status=approved` → records `qr_permit_usages.status=approved`, writes `QrScanLog` (`result=approved`), fires `OrderQrUsed` broadcast event.
- `inspection_status=rejected` → records `qr_permit_usages.status=rejected`, writes `QrScanLog` (`result=rejected`).
- Both statuses log GPS + location into `qr_scan_logs`.
- Marks `orders.qr_used_at` / `qr_used_by_uuid`.

---

## 12. GET `/api/qr-scan/history` 🔒 Auth required

Scan history for the logged-in internal user (non-admin). Only own scans.

**Headers:** `Authorization: Bearer <token>`.

**Query params:**

| Param | Type | Required | Description |
|---|---|---|---|
| `result` | string | no | `approved`, `rejected`, `valid`, `used`, `invalid` — filter by scan outcome |
| `per_page` | int | no | Items per page (default `15`, max `100`) |

**Response `200`:** Laravel paginator under `logs`, each item:

```json
{
  "status": "success",
  "logs": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "scanner": null,
        "permit_number": "IPO/123",
        "order_number": "ORD-0001",
        "application_type": "Import Permit",
        "result": "approved",
        "is_valid": true,
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

`scanned_at` format `d-m-Y h:i:s A`. Logs restricted to the authenticated user's `internal_user_uuid`; `scanner` is `null`.

**Error responses:**

| Code | Body |
|---|---|
| `401` | `{"status":"error","message":"Unauthenticated."}` (missing/expired token) |
| `422` | `{"status":"error","message":"result must be one of: approved, rejected, valid, used, invalid."}` |

---

## 13. GET `/api/qr-scan/history/all` 🔒 Auth required, admins only

All scan history across every internal user. Requires `admin` or `superadmin` role.

**Headers:** `Authorization: Bearer <token>`.

**Query params:** same as `/api/qr-scan/history` (`result`, `per_page`).

**Response `200`:** paginator, each item includes `scanner`:

```json
{
  "status": "success",
  "logs": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "scanner": {
          "name": "Ali Bin Ahmad",
          "roles": "boundary officer"
        },
        "permit_number": "IPO/123",
        "order_number": "ORD-0001",
        "application_type": "Import Permit",
        "result": "approved",
        "is_valid": true,
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

**Error responses:**

| Code | Body |
|---|---|
| `401` | `{"status":"error","message":"Unauthenticated."}` (missing/expired token) |
| `403` | `{"status":"error","message":"Unauthorized: admins only."}` (non-admin token) |
| `422` | `{"status":"error","message":"result must be one of: approved, rejected, valid, used, invalid."}` |

---

## 14. GET `/api/order/details/{order_number}` 🔒 Auth required

Fetch full order details: header, payment, application, permits, QR usage.

**Headers:** `Authorization: Bearer <token>`.

**Path params:**

| Param | Type | Description |
|---|---|---|
| `order_number` | string | Order number, e.g. `ORD-0001` |

**Response `200`:**

```json
{
  "status": "success",
  "header": {
    "order_number": "ORD-0001"
  },
  "order_details": {
    "order_number": "ORD-0001",
    "order_status": "paid",
    "application_id": "APP-0001",
    "permit_id": "IPO/123, IPO/124"
  },
  "payment_details": {
    "seller_ref": "SELLER-REF-1",
    "fpx_seller_reference": "FPX-REF-1",
    "name": "Ali Bin Ahmad",
    "email": "ali@example.com",
    "phone": "+60123456789",
    "payment_amount": "RM 100.00",
    "transaction_data": "-",
    "transaction_status": "success"
  },
  "application_details": {
    "application_id": "APP-0001",
    "exporter_name": "Exporter Sdn Bhd",
    "exporter_number_phone": "+60123456789",
    "exporter_address": "12, Jalan Merdeka",
    "exporter_country": "Malaysia",
    "importer_name": "Importer Co",
    "importer_address": "88, Jalan Import"
  },
  "permit_details": [
    {
      "permit_number": "IPO/123",
      "item_name": "Steel Coil"
    }
  ],
  "qr_info": {
    "is_used": false,
    "used_at": null,
    "used_by_uuid": null
  }
}
```

`payment_amount` formatted as `RM 1,000.00`. `qr_info.used_at` format `d-m-Y h:i:s A`.

**Response `404`:**

```json
{
  "status": "error",
  "message": "Order not found."
}
```

---

## QR Payload Format (`QIS1:`)

Produced by `App\Services\PermitQrService`. Encrypted, MAC-authenticated payload embedded in the permit QR code.

```
QIS1:<base64(JSON)>
```

Where JSON:

```json
{
  "iv": "<base64 16-byte IV>",
  "ct": "<base64 AES-256-CBC ciphertext>",
  "mac": "<base64 sha256 HMAC>"
}
```

**Plaintext JSON inside `ct`:**

```json
{
  "permit_number": "IPO/123",
  "ts": 1735689600,
  "v": 1
}
```

| Field | Type | Description |
|---|---|---|
| `permit_number` | string | Issued permit number |
| `ts` | int | Unix timestamp of encryption |
| `v` | int | Payload version (currently `1`) |

**Crypto scheme:**

- Key: `sha256(QIS_QR_KEY)` from `config/services.php` → `services.qis.qr_key` (env `QIS_QR_KEY`). Raw binary 32 bytes.
- Encrypt: `AES-256-CBC`, `OPENSSL_RAW_DATA`, random 16-byte IV.
- MAC: `HMAC-SHA256(iv . ciphertext, key)` — verified with `hash_equals` before decrypt.
- QR code: PNG data URI, UTF-8, error correction Medium, size 300, margin 10.

**Verification flow (`GET /api/permit/validate` with `qr_payload`):**

1. Prefix must be `QIS1:`.
2. Base64-decode wrapper; require `iv`, `ct`, `mac`.
3. Verify HMAC; reject `QR payload MAC mismatch.` if tampered.
4. Decrypt; parse JSON; require `permit_number`.
5. Fall back to normal permit-number lookup.

Env vars: `QIS_QR_KEY` (required), `QIS_QR_ENFORCE_ONE_TIME` (default `true`).

---

## Auth Notes

- **Token auth (Sanctum):** `POST /api/internal/login` returns a Bearer token (24h, or 30d with `remember_me`). Send as `Authorization: Bearer <token>` on protected routes. `POST /api/internal/logout` revokes it.
- 🔒 Protected: `/api/permit/validate`, `/api/permits/pending`, `/api/qr-scan/complete-scan`, `/api/qr-scan/history`, `/api/qr-scan/history/all`, `/api/order/details/{order_number}`, `/api/internal/logout` — `auth:internal-api`.
- Public: `/api/internal/login`, `/api/states`, `/api/districts/{stateId}`, `/api/all-districts`.
- **Scan logging:** every scanner call writes `qr_scan_logs` — `validate` logs `valid` / `used` / `invalid`; `complete-scan` logs `approved` / `rejected` (with GPS + location).
- `GET /api/user` requires `auth:sanctum`.
