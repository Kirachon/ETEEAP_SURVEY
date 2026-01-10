# API Documentation

JSON endpoints for the ETEEAP Survey Application.

## Base URL

- Base: `/api`

## Authentication

Admin JSON endpoints require an authenticated admin session cookie.
Log in via:
- `GET /admin/login` (page)
- `POST /admin/login` (form)

## Admin JSON Endpoints (requires admin session)

### Summary

- `GET /api/stats/summary`

Returns overall counters for the dashboard (counts + derived rates).

### Demographics

- `GET /api/stats/demographics`

Returns distributions for age, sex, office type, employment status, years, and education.

### Interest

- `GET /api/stats/interest`

Returns interest levels, awareness, and top motivations/barriers.

### Timeline

- `GET /api/stats/timeline`

Returns daily/weekly/monthly completed-response counts.

## Public JSON Endpoints (no auth)

### Positions (searchable)

- `GET /api/positions?q=<search>&limit=<n>`

Reads from `docs/update/positions.csv`.

### Courses (searchable)

- `GET /api/courses?q=<search>&limit=<n>`

Reads from `docs/update/academy_course.csv`.

## Public Survey Submission (CSRF-protected, no auth)

### Submit Survey Response

- `POST /api/survey/submit`

This endpoint is intended for automation/integration. The normal survey flow is still the multi-step form (`/survey/*`).

Requirements:
- A valid session cookie (from an initial GET request to any page, e.g. `/survey/consent`)
- A CSRF token from the HTML (`<meta name="csrf-token" content="...">` or hidden input)
- Send `X-CSRF-Token: <token>` header

#### Request headers

```http
Content-Type: application/json
X-CSRF-Token: {csrf_token}
```

#### Request body (schema)

```json
{
  "session_id": "optional-string",
  "consent_given": true,
  "basic_info": {
    "first_name": "JUAN",
    "middle_name": "SANTOS",
    "last_name": "DELA CRUZ",
    "ext_name": "Jr.",
    "sex": "male",
    "age_range": "30-39",
    "email": "juan@example.com",
    "phone": "+63 912 345 6789"
  },
  "office_data": {
    "office_type": "field_office",
    "office_assignment": "FO VII",
    "specific_office": "DSWD FIELD OFFICE VII",
    "current_position": "SOCIAL WELFARE OFFICER III",
    "employment_status": "permanent",
    "program_assignments": ["4Ps", "SLP"]
  },
  "work_experience": {
    "years_dswd": "5-10",
    "years_swd_sector": "lt5",
    "sw_tasks": ["Case management / casework"]
  },
  "competencies": {
    "expertise_areas": ["Case management (assessment, intervention, referral)"]
  },
  "education": {
    "highest_education": "bachelors",
    "undergrad_course": "BS PSYCHOLOGY"
  },
  "dswd_training": {
    "availed_dswd_training": true,
    "courses": ["Basic Social Work"]
  },
  "eteeap_interest": {
    "eteeap_awareness": true,
    "interest_level": "very_interested",
    "motivations": ["career_advancement"],
    "barriers": ["time_constraints"],
    "will_apply": "no",
    "will_not_apply_reason": "Workload constraints and limited time at the moment.",
    "additional_comments": "Optional comment"
  }
}
```

#### Allowed values (selected)

- `office_type`: `central_office` | `field_office` | `attached_agency`
- `employment_status`: `permanent` | `cos` | `jo` | `others`
- `years_dswd` / `years_swd_sector`: `lt5` | `5-10` | `11-15` | `15+`
- `interest_level`: `very_interested` | `interested` | `somewhat_interested` | `not_interested`
- `will_apply`: `yes` | `no` (if `no`, `will_not_apply_reason` is required)

#### Responses

Success (201):
```json
{ "success": true, "message": "Survey submitted successfully", "response_id": 123 }
```

Validation error (400):
```json
{ "success": false, "message": "Validation failed", "errors": { "field": ["message"] } }
```

Duplicate (409):
```json
{ "success": false, "message": "Duplicate submission", "errors": { "email": ["..."] } }
```

#### cURL example (cookie + CSRF)

```bash
curl -s -c cookies.txt http://localhost:8000/survey/consent -o consent.html
CSRF_TOKEN=$(php -r "echo (preg_match('/name=\"csrf-token\" content=\"([^\"]+)\"/', file_get_contents('consent.html'), $m) ? $m[1] : '');")
curl -s -b cookies.txt -H "Content-Type: application/json" -H "X-CSRF-Token: $CSRF_TOKEN" \
  -d @payload.json http://localhost:8000/api/survey/submit
```

