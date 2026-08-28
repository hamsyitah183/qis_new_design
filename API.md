# QIS API Reference

Base URL: `https://your-host/api`

All responses are JSON. Unless noted, endpoints are unauthenticated except `GET /api/user`.

---

## 1. POST `/api/login`

Authenticate a public or internal user. Stateless (no session created); returns a user payload for the mobile app.

**Request body (JSON):**

| Field | Type | Required | Rules |
|---|---|---|---|
| `userType` | string | yes | `in:public,internal` |
| `email` | string | yes | valid email |
| `password` | string | yes | string |
| `lang` | string | no | default `en` |

**Example payload:**

```json
{
  "userType": "internal",
  "email": "scanner@qis.gov.my",
  "password": "secret123",
  "lang": "en"
}
```

**Response `200` (success):**

```json
{
  "status": "success",
  "message": "Login successful.",
  "user": {
    "uuid": "6f0c...",
    "name": "Ali Bin Ahmad",
    "email": "scanner@qis.gov.my",
    "userType": "internal"
  }
}
```

**Response `200` (email unverified):** status `unverified`, includes `redirect` to verify page.

```json
{
  "status": "unverified",
  "message": "Please verify your email.",
  "redirect": "http://host/verify-email"
}
```

**Response `422` (errors):**

```json
{
  "status": "error",
  "message": "Invalid credentials."
}
```

Error messages are translated based on `lang` (en/ms).

---

## 2. GET `/api/user`

Return the currently authenticated user. Requires `auth:sanctum` (Bearer token).

**Headers:**

| Header | Value |
|---|---|
| `Authorization` | `Bearer <token>` |
| `Accept` | `application/json` |

**Response `200`:** The authenticated `PublicUser` / `InternalUser` model as JSON.

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

## 9. GET `/api/permit/validate`

Validate a permit by number or by encrypted QR payload. Used by the scanner app.

**Query params:**

| Param | Type | Required | Description |
|---|---|---|---|
| `permit_number` | string | yes* | Permit number, e.g. `IPO/123` or `IPO123` |
| `qr_payload` | string | yes* | Encrypted `QIS1:` payload (see QR Payload Format below). Overrides `permit_number` |
| `scanner_user_type` | string | no | Must be `internal` to allow one-time QR consumption |
| `scanner_user_uuid` | string | no | Internal user UUID; required for full validation |

\* At least one of `permit_number` / `qr_payload` required.

**Examples:**

```
GET /api/permit/validate?permit_number=IPO/123
GET /api/permit/validate?qr_payload=QIS1:eyJpdiI6...
GET /api/permit/validate?permit_number=IPO/123&scanner_user_type=internal&scanner_user_uuid=6f0c...
```

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
  "action": "endorsed",
  "endorsed_by": "Ali Bin Ahmad",
  "endorsed_at": "01-01-2025 10:30:00 AM",
  "endorsed_location": "Port Klang Gate 3",
  "endorsed_lat": 3.0,
  "endorsed_lng": 101.4
}
```

`application_type` values: `Import Permit` | `Inspection Certificate` | `Consignment Certificate`.

Permit lookup tables searched in order: `IpConsignmentPermit`, `InspectionItem`, `ConsignmentPermit`. Format is normalized (uppercase, spaces removed, `/` ignored).

---

## 10. GET `/api/permits/pending`

List issued permits (status `paid`) not yet endorsed/ignored, grouped by application type. For scanner pending screen.

**Query params:**

| Param | Type | Required | Description |
|---|---|---|---|
| `scanner_user_type` | string | yes | Must be `internal` |
| `scanner_user_uuid` | string | yes | Internal user UUID |
| `application_type` | string | no | Filter to one type; empty = all |

**Example:**

```
GET /api/permits/pending?scanner_user_type=internal&scanner_user_uuid=6f0c...
GET /api/permits/pending?scanner_user_type=internal&scanner_user_uuid=6f0c...&application_type=Import%20Permit
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

**Response `403`:**

```json
{
  "status": "error",
  "message": "Internal scanner identity is required."
}
```

---

## 11. POST `/api/qr-scan/complete-scan`

Record the result of a scanned permit inspection (approve/reject), with live GPS. Consumes the QR one-time.

**Query params:**

| Param | Type | Required | Description |
|---|---|---|---|
| `scanner_user_type` | string | yes | Must be `internal` |
| `scanner_user_uuid` | string | yes | Internal user UUID |

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
| `422` | `{"status":"error","message":"permit_number, order_number, and application_type are required."}` |
| `422` | `{"status":"error","message":"inspection_status must be either \"approved\" or \"rejected\"."}` |
| `422` | `{"status":"error","message":"A live GPS location is required to record this scan."}` |
| `403` | `{"status":"error","message":"Internal scanner identity is required."}` |
| `403` | `{"status":"error","message":"Invalid scanner user."}` |
| `404` | `{"status":"error","message":"Order not found."}` |
| `409` | `{"status":"error","message":"QR code has already been used."}` |
| `500` | `{"status":"error","message":"Failed to log scan completion."}` |

Behavior:
- `inspection_status=approved` → records `endorsed`, writes `QrScanLog`, fires `OrderQrUsed` broadcast event.
- `inspection_status=rejected` → records `ignored`, no log, no event.
- Marks `orders.qr_used_at` / `qr_used_by_uuid`.

---

## 12. GET `/api/order/details/{order_number}`

Fetch full order details: header, payment, application, permits, QR usage.

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

- `/api/login` returns user identity (no token). Sanctum token issuance not shown in these routes.
- `GET /api/user` requires `auth:sanctum` middleware.
- Scanner endpoints (`/permit/validate`, `/permits/pending`, `/qr-scan/complete-scan`) gate one-time QR actions behind `scanner_user_type=internal` + valid `scanner_user_uuid`.
