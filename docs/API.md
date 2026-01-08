# API Documentation

> REST API endpoints for the ETEEAP Survey Application

---

## 📋 Overview

The ETEEAP Survey API provides endpoints for retrieving survey statistics, demographics, and response data. All API endpoints return JSON responses.

**Base URL**: `/api`

---

## 🔐 Authentication

Admin API endpoints require session-based authentication. Users must be logged in through the admin panel to access these endpoints.

---

## 📊 Statistics Endpoints

### Get Dashboard Summary

Retrieves overall statistics for the dashboard.

**Endpoint**: `GET /api/stats/summary`

**Authentication**: Required

**Response**:
```json
{
  "success": true,
  "data": {
    "total_responses": 245,
    "completion_rate": 92.5,
    "consent_rate": 95.2,
    "week_responses": 18,
    "very_interested": 89,
    "will_apply": 127
  }
}
```

**Fields**:
- `total_responses` (integer): Total number of completed surveys
- `completion_rate` (float): Percentage of surveys completed
- `consent_rate` (float): Percentage of users who consented
- `week_responses` (integer): Responses received this week
- `very_interested` (integer): Count of "very interested" respondents
- `will_apply` (integer): Count of respondents who will apply

---

### Get Demographics Data

Retrieves demographic breakdowns for visualizations.

**Endpoint**: `GET /api/stats/demographics`

**Authentication**: Required

**Response**:
```json
{
  "success": true,
  "data": {
    "sex_distribution": [
      {"label": "male", "value": "120"},
      {"label": "female", "value": "125"}
    ],
    "age_distribution": [
      {"label": "20-29", "value": "45"},
      {"label": "30-39", "value": "98"},
      {"label": "40-49", "value": "67"},
      {"label": "50-59", "value": "28"},
      {"label": "60+", "value": "7"}
    ],
    "office_type_distribution": [
      {"label": "central_office", "value": "89"},
      {"label": "field_office", "value": "142"},
      {"label": "attached_agency", "value": "14"}
    ]
  }
}
```

**Data Fields**:
- `sex_distribution`: Gender breakdown
- `age_distribution`: Age range breakdown
- `office_type_distribution`: Office type breakdown

---

### Get Interest Data

Retrieves ETEEAP interest-related statistics.

**Endpoint**: `GET /api/stats/interest`

**Authentication**: Required

**Response**:
```json
{
  "success": true,
  "data": {
    "interest_levels": [
      {"label": "very_interested", "value": "89"},
      {"label": "interested", "value": "67"},
      {"label": "somewhat_interested", "value": "34"},
      {"label": "not_interested", "value": "10"}
    ],
    "top_motivations": [
      {"label": "career_advancement", "value": "145"},
      {"label": "professional_development", "value": "132"},
      {"label": "salary_increase", "value": "98"},
      {"label": "recognition", "value": "76"}
    ],
    "top_barriers": [
      {"label": "time_constraints", "value": "87"},
      {"label": "financial_limitations", "value": "65"},
      {"label": "family_obligations", "value": "54"},
      {"label": "lack_of_information", "value": "32"}
    ]
  }
}
```

---

## 📝 Response Endpoints

### Get All Responses

Retrieves a paginated list of all survey responses.

**Endpoint**: `GET /api/responses`

**Authentication**: Required

**Query Parameters**:
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 20, max: 100)
- `search` (string, optional): Search by name or email
- `office_type` (string, optional): Filter by office type
- `interest` (string, optional): Filter by interest level

**Example Request**:
```http
GET /api/responses?page=1&per_page=20&search=Juan&office_type=field_office
```

**Response**:
```json
{
  "success": true,
  "data": {
    "responses": [
      {
        "id": 123,
        "first_name": "Juan",
        "last_name": "Dela Cruz",
        "email": "juan@example.com",
        "office_type": "field_office",
        "eteeap_interest": "very_interested",
        "completed_at": "2026-01-08 14:30:00",
        "created_at": "2026-01-08 14:15:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 245,
      "total_pages": 13,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

---

### Get Single Response

Retrieves detailed information for a specific response.

**Endpoint**: `GET /api/responses/{id}`

**Authentication**: Required

**Path Parameters**:
- `id` (integer, required): Response ID

**Example Request**:
```http
GET /api/responses/123
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 123,
    "session_id": "abc123def456",
    "consent_given": true,
    "completed_at": "2026-01-08 14:30:00",
    
    "basic_info": {
      "first_name": "Juan",
      "middle_name": "Santos",
      "last_name": "Dela Cruz",
      "ext_name": "Jr.",
      "sex": "male",
      "age_range": "30-39",
      "email": "juan@example.com",
      "phone": "+63 912 345 6789"
    },
    
    "office_data": {
      "office_type": "field_office",
      "specific_office": "Field Office VII",
      "current_position": "Social Welfare Officer III",
      "employment_status": "permanent",
      "program_assignments": ["DSWD-KCSP", "Pantawid Pamilya"]
    },
    
    "work_experience": {
      "years_dswd": "6-10",
      "years_swd_sector": "5-10"
    },
    
    "competencies": {
      "performs_sw_tasks": true,
      "sw_tasks": ["case_management", "counseling", "community_organizing"],
      "expertise_areas": ["child_welfare", "disaster_response"]
    },
    
    "education": {
      "highest_education": "bachelors",
      "undergrad_course": "BS Psychology",
      "diploma_course": "Diploma in Social Work",
      "graduate_course": null
    },
    
    "dswd_training": {
      "availed_dswd_training": true,
      "courses": ["Basic Social Work", "Case Management", "DRRM Training"]
    },
    
    "eteeap_interest": {
      "eteeap_awareness": true,
      "interest_level": "very_interested",
      "motivations": ["career_advancement", "professional_development"],
      "barriers": ["time_constraints", "financial_limitations"],
      "will_apply": "yes",
      "additional_comments": "Very excited about this opportunity!"
    },
    
    "metadata": {
      "created_at": "2026-01-08 14:15:00",
      "updated_at": "2026-01-08 14:30:00"
    }
  }
}
```

---

### Export Responses to CSV

Generates and downloads a CSV file of all responses.

**Endpoint**: `POST /api/responses/export`

**Authentication**: Required

**Request Body** (optional):
```json
{
  "filters": {
    "office_type": "field_office",
    "interest": "very_interested",
    "date_from": "2026-01-01",
    "date_to": "2026-01-31"
  }
}
```

**Response**:
```
Content-Type: text/csv
Content-Disposition: attachment; filename="responses_2026-01-08.csv"

ID,First Name,Last Name,Email,Office Type,Interest Level,Completed At
123,Juan,Dela Cruz,juan@example.com,field_office,very_interested,2026-01-08 14:30:00
124,Maria,Santos,maria@example.com,central_office,interested,2026-01-08 15:45:00
```

**CSV Columns**:
- Basic information (ID, Name, Email, Phone)
- Office data (Type, Specific Office, Position, Status)
- Programs (comma-separated)
- Work experience (Years in DSWD, Years in SWD sector)
- Competencies (SW Tasks performed, Expertise areas)
- Education (Highest level, Courses)
- DSWD Training (Courses taken)
- ETEEAP Interest (Awareness, Level, Motivations, Barriers, Will Apply)
- Timestamps (Created, Completed)

---

## 🔒 Survey Submission Endpoint

### Submit Survey Response

Submits a complete survey response.

**Endpoint**: `POST /api/survey/submit`

**Authentication**: Not required (uses CSRF token)

**Request Headers**:
```http
Content-Type: application/json
X-CSRF-Token: {csrf_token}
```

**Request Body**:
```json
{
  "session_id": "abc123def456",
  "consent_given": true,
  "basic_info": {
    "first_name": "Juan",
    "middle_name": "Santos",
    "last_name": "Dela Cruz",
    "sex": "male",
    "age_range": "30-39",
    "email": "juan@example.com",
    "phone": "+63 912 345 6789"
  },
  "office_data": {
    "office_type": "field_office",
    "specific_office": "Field Office VII",
    "current_position": "Social Welfare Officer III",
    "employment_status": "permanent",
    "program_assignments": ["DSWD-KCSP", "Pantawid Pamilya"]
  },
  "work_experience": {
    "years_dswd": "6-10",
    "years_swd_sector": "5-10"
  },
  "competencies": {
    "performs_sw_tasks": true,
    "sw_tasks": ["case_management", "counseling"],
    "expertise_areas": ["child_welfare"]
  },
  "education": {
    "highest_education": "bachelors",
    "undergrad_course": "BS Psychology"
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
    "will_apply": "yes",
    "additional_comments": "Very excited!"
  }
}
```

**Success Response** (201 Created):
```json
{
  "success": true,
  "message": "Survey submitted successfully",
  "response_id": 125
}
```

**Error Response** (400 Bad Request):
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email is already registered"],
    "office_type": ["Office type is required"]
  }
}
```

---

## ⚠️ Error Responses

### Standard Error Format

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes

| Status Code | Meaning |
|-------------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request (validation error) |
| 401 | Unauthorized (not logged in) |
| 403 | Forbidden (insufficient permissions) |
| 404 | Not Found |
| 500 | Internal Server Error |

---

## 💡 Usage Examples

### JavaScript (Fetch API)

**Get Dashboard Stats**:
```javascript
async function getDashboardStats() {
  const response = await fetch('/api/stats/summary', {
    method: 'GET',
    credentials: 'same-origin' // Include session cookie
  });
  
  const data = await response.json();
  
  if (data.success) {
    console.log('Total responses:', data.data.total_responses);
  }
}
```

**Export Responses**:
```javascript
async function exportResponses() {
  const response = await fetch('/api/responses/export', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      filters: {
        office_type: 'field_office'
      }
    })
  });
  
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'responses.csv';
  a.click();
}
```

### PHP (cURL)

**Get Response Details**:
```php
<?php
$ch = curl_init('http://survey.example.com/api/responses/123');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data['success']) {
    echo "Email: " . $data['data']['basic_info']['email'];
}

curl_close($ch);
?>
```

---

## 🔄 Rate Limiting

Currently, no rate limiting is implemented. For production deployments, consider implementing rate limiting on API endpoints to prevent abuse.

**Recommended Limits**:
- Statistics endpoints: 60 requests/minute
- Response endpoints: 30 requests/minute
- Export endpoint: 5 requests/minute

---

## 📝 Notes

- All datetime fields are in MySQL `TIMESTAMP` format: `YYYY-MM-DD HH:MM:SS`
- Multi-value fields (checkboxes) are returned as arrays
- Empty/null values are omitted from responses
- CSRF tokens are automatically included in session
- For survey submission outside the web form, obtain CSRF token from `/api/csrf-token` endpoint (not documented here as it's internal)

---

**Last Updated**: January 2026  
**API Version**: 1.0.0
