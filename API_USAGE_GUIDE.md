# API Usage Guide

## Authentication

All API requests must include your API key in one of two ways:

### Option 1: X-API-Key Header (Recommended)
```bash
curl -X GET https://your-domain.com/api/review-requests \
  -H "X-API-Key: tk_your_64_character_api_key_here" \
  -H "Accept: application/json"
```

### Option 2: Bearer Token
```bash
curl -X GET https://your-domain.com/api/review-requests \
  -H "Authorization: Bearer tk_your_64_character_api_key_here" \
  -H "Accept: application/json"
```

## Permissions

Each API key has specific permissions. When you create an API key, you select which permissions it should have:

- `review-requests.create` - Create new review requests
- `review-requests.read` - View review requests
- `review-requests.update` - Update existing review requests
- `review-requests.delete` - Delete review requests
- `customers.create` - Create new customers
- `customers.read` - View customers

### Permission Requirements by Endpoint

| Endpoint | Method | Permission Required |
|----------|--------|---------------------|
| `/api/review-requests` | GET | `review-requests.read` |
| `/api/review-requests` | POST | `review-requests.create` |
| `/api/review-requests/{id}` | GET | `review-requests.read` |
| `/api/review-requests/{id}` | PUT | `review-requests.update` |
| `/api/review-requests/{id}` | DELETE | `review-requests.delete` |
| `/api/review-requests/{id}/send` | POST | `review-requests.create` |

## Error Responses

### 401 Unauthorized - Missing or Invalid API Key
```json
{
  "message": "API key is required. Provide it in X-API-Key header or Authorization: Bearer token"
}
```

or

```json
{
  "message": "Invalid or expired API key"
}
```

### 403 Forbidden - Insufficient Permissions
```json
{
  "message": "API key does not have the required permission: review-requests.create"
}
```

## Examples

### 1. Create a Review Request (Immediate Send)

**Permission Required:** `review-requests.create`

```bash
curl -X POST https://your-domain.com/api/review-requests \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "customer": {
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    },
    "subject": "How was your experience?",
    "message": "We would love to hear your feedback!",
    "send_mode": "now"
  }'
```

**Response (201 Created):**
```json
{
  "message": "Review request created successfully",
  "data": {
    "id": 123,
    "customer_id": 456,
    "subject": "How was your experience?",
    "message": "We would love to hear your feedback!",
    "send_mode": "now",
    "sent_at": "2025-11-14T10:30:00.000000Z",
    "customer": {
      "id": 456,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

### 2. Create a Scheduled Review Request

**Permission Required:** `review-requests.create`

```bash
curl -X POST https://your-domain.com/api/review-requests \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "customer": {
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "subject": "How was your appointment?",
    "message": "Please share your experience with us!",
    "send_mode": "scheduled",
    "schedule_hours": 24
  }'
```

### 3. List Review Requests

**Permission Required:** `review-requests.read`

```bash
curl -X GET https://your-domain.com/api/review-requests \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Accept: application/json"
```

**Optional Query Parameters:**
- `status` - Filter by status (pending, sent, responded, expired)
- `send_mode` - Filter by send mode (now, scheduled, manual)
- `per_page` - Number of results per page (default: 15)
- `page` - Page number

**Response (200 OK):**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 123,
      "customer_id": 456,
      "subject": "How was your experience?",
      "status": "sent",
      "customer": {
        "id": 456,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "per_page": 15,
  "total": 1
}
```

### 4. Get a Single Review Request

**Permission Required:** `review-requests.read`

```bash
curl -X GET https://your-domain.com/api/review-requests/123 \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Accept: application/json"
```

### 5. Send a Manual/Scheduled Review Request

**Permission Required:** `review-requests.create`

```bash
curl -X POST https://your-domain.com/api/review-requests/123/send \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Accept: application/json"
```

### 6. Update a Review Request (Reschedule)

**Permission Required:** `review-requests.update`

```bash
curl -X PUT https://your-domain.com/api/review-requests/123 \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "send_mode": "scheduled",
    "schedule_hours": 48
  }'
```

### 7. Delete a Review Request

**Permission Required:** `review-requests.delete`

```bash
curl -X DELETE https://your-domain.com/api/review-requests/123 \
  -H "X-API-Key: tk_your_api_key_here" \
  -H "Accept: application/json"
```

## Integration Examples

### PHP (with Guzzle)

```php
<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://your-domain.com/api/',
    'headers' => [
        'X-API-Key' => 'tk_your_api_key_here',
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]
]);

// Create a review request
$response = $client->post('review-requests', [
    'json' => [
        'customer' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
        'subject' => 'How was your experience?',
        'message' => 'We would love to hear your feedback!',
        'send_mode' => 'now',
    ]
]);

$result = json_decode($response->getBody(), true);
echo "Review request created with ID: " . $result['data']['id'];
```

### JavaScript (Node.js with Axios)

```javascript
const axios = require('axios');

const api = axios.create({
  baseURL: 'https://your-domain.com/api/',
  headers: {
    'X-API-Key': 'tk_your_api_key_here',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Create a review request
async function createReviewRequest() {
  try {
    const response = await api.post('review-requests', {
      customer: {
        name: 'John Doe',
        email: 'john@example.com',
      },
      subject: 'How was your experience?',
      message: 'We would love to hear your feedback!',
      send_mode: 'now',
    });
    
    console.log('Review request created:', response.data);
  } catch (error) {
    if (error.response) {
      console.error('Error:', error.response.data.message);
    }
  }
}

createReviewRequest();
```

### Python (with Requests)

```python
import requests

API_KEY = 'tk_your_api_key_here'
BASE_URL = 'https://your-domain.com/api/'

headers = {
    'X-API-Key': API_KEY,
    'Accept': 'application/json',
    'Content-Type': 'application/json',
}

# Create a review request
data = {
    'customer': {
        'name': 'John Doe',
        'email': 'john@example.com',
    },
    'subject': 'How was your experience?',
    'message': 'We would love to hear your feedback!',
    'send_mode': 'now',
}

response = requests.post(
    f'{BASE_URL}review-requests',
    headers=headers,
    json=data
)

if response.status_code == 201:
    result = response.json()
    print(f"Review request created with ID: {result['data']['id']}")
else:
    print(f"Error: {response.json()['message']}")
```

## Best Practices

1. **Keep Your API Key Secret**: Never commit your API key to version control or expose it in client-side code.

2. **Use HTTPS**: Always use HTTPS in production to encrypt API requests.

3. **Handle Rate Limits**: Implement exponential backoff if you receive rate limit errors.

4. **Least Privilege**: Create API keys with only the permissions needed for your integration.

5. **Rotate Keys Regularly**: Periodically revoke old keys and create new ones.

6. **Monitor Usage**: Check the "Last Used" timestamp in the dashboard to detect unauthorized access.

7. **Set Expiration**: For temporary integrations, set an expiration date on your API keys.

## Security

- API keys are hashed using bcrypt before storage
- Only the first creation shows the full key
- Keys are validated using constant-time comparison
- Each request updates the `last_used_at` and `last_used_ip` for audit purposes
- Expired or revoked keys are immediately rejected
