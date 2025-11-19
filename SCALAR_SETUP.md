# Scalar API Documentation Setup

## Installation Complete ✓

Scalar has been successfully installed and configured to use your Scribe-generated OpenAPI specification.

## What Was Done

1. **Installed Scalar Package**
   ```bash
   composer require scalar/laravel
   ```

2. **Published Configuration**
   ```bash
   php artisan vendor:publish --tag="scalar-config"
   ```

3. **Configured OpenAPI Source**
   - Updated `config/scalar.php` to use Scribe's OpenAPI endpoint: `/docs.openapi`

4. **Added Authorization Gate**
   - Added `viewScalar` gate in `AppServiceProvider.php`
   - Currently allows access in local environment
   - Can be customized to restrict access in production by adding authorized emails

5. **Generated API Documentation**
   - Ran `php artisan scribe:generate` to create the OpenAPI spec
   - OpenAPI spec is stored at: `storage/app/private/scribe/openapi.yaml`

## Available Routes

### Scalar (New UI)
- **URL:** `/scalar`
- **Description:** Modern API reference UI powered by Scalar
- **Authorization:** Controlled by `viewScalar` gate (currently allows local environment)

### Scribe (Original)
- **Documentation:** `/docs` - Original Scribe HTML documentation
- **OpenAPI Spec:** `/docs.openapi` - OpenAPI YAML specification (used by Scalar)
- **Postman Collection:** `/docs.postman` - Postman collection export

## Usage

1. **Access Scalar Documentation:**
   - Visit: `http://localhost:8000/scalar` (or your app URL)
   - You'll see your API documentation with Scalar's modern, interactive UI

2. **Authenticate Requests:**
   - Click the "Auth" or lock icon in the Scalar UI
   - Enter your API key in the authentication dialog
   - The API key will be automatically sent as an `X-API-Key` header with all test requests
   - Alternatively, you can use the API key as a Bearer token in the `Authorization` header

3. **Update Documentation:**
   ```bash
   php artisan scribe:generate
   ```
   This regenerates the OpenAPI spec that Scalar uses

## Authentication Configuration

The API uses API Key authentication via the `X-API-Key` header (or Bearer token). The authentication is configured in:

- **Middleware:** `App\Http\Middleware\AuthenticateApiKey`
- **Header Name:** `X-API-Key`
- **Alternative:** Bearer token in `Authorization` header
- **Scribe Config:** Enabled in `config/scribe.php` with authentication details
- **Scalar Config:** Authentication UI enabled in `config/scalar.php`

### How it Works:
1. Users enter their API key in the Scalar authentication dialog
2. Scalar automatically includes the key in the `X-API-Key` header for all API test requests
3. The middleware validates the key and attaches the associated business to the request
4. Protected endpoints can specify required permissions via middleware parameters

## Configuration Files

### Scalar Config (`config/scalar.php`)
- **Path:** `/scalar` - Route path for Scalar
- **Theme:** `laravel` - Using Laravel theme
- **Layout:** `modern` - Modern layout style
- **OpenAPI URL:** `/docs.openapi` - Points to Scribe's OpenAPI endpoint

### Scribe Config (`config/scribe.php`)
- **Type:** `laravel` - Generates Blade views
- **OpenAPI:** Enabled and generated at `/docs.openapi`
- **Routes:** Matches `api/*` routes

## Authorization (Production)

To restrict access in production, update the gate in `app/Providers/AppServiceProvider.php`:

```php
Gate::define('viewScalar', function (?User $user) {
    return in_array($user?->email, [
        'admin@example.com',
        'developer@example.com',
    ]);
});
```

## Next Steps

1. **Customize Scalar Appearance:** Edit `config/scalar.php` to change theme, layout, etc.
2. **Add API Authentication:** Configure authentication methods in Scalar config
3. **Publish Views (Optional):** 
   ```bash
   php artisan vendor:publish --tag="scalar-views"
   ```

## Benefits Over Scribe UI

- ✅ Modern, clean interface
- ✅ Better interactive API testing
- ✅ **Interactive authentication screen** - Easy API key management
- ✅ Improved mobile experience
- ✅ More customization options
- ✅ Active development and updates

## Testing API Endpoints in Scalar

1. **Open Scalar:** Navigate to `/scalar`
2. **Authenticate:**
   - Look for the authentication section or lock icon
   - Enter your API key
   - Scalar will automatically include it in all requests
3. **Test Endpoints:**
   - Select any endpoint from the sidebar
   - Click "Send Request" or "Try It"
   - View the real-time response
4. **Manage Authentication:**
   - Update or remove API keys as needed
   - Switch between different API keys for testing

You can now use Scalar's beautiful UI while still leveraging Scribe's powerful documentation generation!
