# WordPress Transients Utilities

A lightweight WordPress library for working with transients and caching. Provides clean APIs for transient operations, bulk management, pattern-based deletion, and high-level caching patterns with WordPress-style global functions.

## Features

* 🎯 **Clean API**: WordPress-style snake_case methods and global functions
* 🚀 **Core Operations**: Type casting, numeric operations, essential utilities
* 📊 **Bulk Management**: Process multiple transients efficiently
* 🔍 **Pattern Matching**: Delete by prefix and custom patterns
* 📈 **Essential Tools**: Size calculation, type detection, boolean toggles
* 💾 **Remember Pattern**: High-level caching with automatic computation
* 🌍 **Global Functions**: WordPress-style helper functions for common operations

## Installation

```bash
composer require arraypress/wp-transient-utils
```

## Quick Start (Global Functions)

The fastest way to get started is with the WordPress-style global functions:

```php
// Remember pattern - get from cache or compute
$expensive_data = wp_cache_remember( 'user_analytics_123', function () {
	return perform_complex_analytics_query( 123 );
}, HOUR_IN_SECONDS );

// Auto-generated cache keys
$user_stats = wp_cache_remember_by( 'user_stats', function () use ( $user_id ) {
	return calculate_user_statistics( $user_id );
}, HOUR_IN_SECONDS, $user_id );

// Increment counters
$page_views = wp_transient_increment( 'page_views_' . $post_id );

// Toggle feature flags
$maintenance_mode = wp_transient_toggle( 'maintenance_mode', HOUR_IN_SECONDS );
```

## Class-Based API

For more advanced usage, use the class-based API:

### Single Transient Operations

```php
use ArrayPress\TransientsUtils\Transient;
use ArrayPress\TransientsUtils\Cache;

// Basic operations
$exists = Transient::exists( 'my_transient' );
$value  = Transient::get( 'my_transient' );
$value  = Transient::get_with_default( 'my_transient', 'default' );

// Type casting
$int_value   = Transient::get_cast( 'my_transient', 'int', 0 );
$array_value = Transient::get_cast( 'my_transient', 'array', [] );
$bool_value  = Transient::get_cast( 'my_transient', 'bool', false );

// Set/delete
Transient::set( 'my_transient', 'value', 3600 );
Transient::delete( 'my_transient' );

// Numeric operations
$new_value = Transient::increment_value( 'counter', 1, 3600 );
$new_value = Transient::decrement_value( 'counter', 1, 3600 );

// Utility methods
$type    = Transient::get_type( 'my_transient' );       // 'string', 'array', etc.
$size    = Transient::get_size( 'my_transient' );       // Size in bytes
$toggled = Transient::toggle( 'boolean_flag', 3600 );   // Toggle boolean value

// Validation helpers
$is_array = Transient::is_type( 'my_transient', 'array' );  // Check if specific type
$is_large = Transient::is_large( 'my_transient' );          // Check if > 1MB
$is_large = Transient::is_large( 'my_transient', 512000 );  // Check if > 500KB
```

### High-Level Caching (Remember Pattern)

```php
// The remember pattern - get from cache or compute and store
$expensive_data = Cache::remember( 'user_analytics_123', function () {
	// This expensive operation only runs on cache miss
	return perform_complex_analytics_query( 123 );
}, HOUR_IN_SECONDS );

// Remember with automatic key generation
$user_stats = Cache::remember_by( 'user_stats', function () use ( $user_id ) {
	return calculate_user_statistics( $user_id );
}, HOUR_IN_SECONDS, $user_id );

// Complex key generation with multiple parameters
$filtered_posts = Cache::remember_by( 'filtered_posts', function () use ( $category, $tag, $limit ) {
	return get_complex_filtered_posts( $category, $tag, $limit );
}, 30 * MINUTE_IN_SECONDS, $category, $tag, $limit );
```

### Multiple Transient Operations

```php
use ArrayPress\TransientsUtils\Transients;

// Bulk operations
$existing      = Transients::exists( [ 'trans1', 'trans2', 'trans3' ] );
$values        = Transients::get( [ 'trans1', 'trans2' ] );
$set_results   = Transients::set( [ 'key1' => 'value1', 'key2' => 'value2' ], 3600 );
$deleted_count = Transients::delete( [ 'trans1', 'trans2' ] );

// Discovery
$all_transients = Transients::get_all();                    // Get all transients
$all_names      = Transients::get_all( false );             // Get names only
$prefixed       = Transients::get_by_prefix( 'my_plugin_' ); // Get by prefix

// Pattern-based deletion
$deleted = Transients::delete_by_prefix( 'my_plugin_' );           // Delete by prefix
$deleted = Transients::delete_by_pattern( 'cache_', 'prefix' );    // Custom patterns
$deleted = Transients::delete_by_pattern( '_temp', 'suffix' );     // Suffix pattern
$deleted = Transients::delete_by_pattern( 'user', 'substring' );   // Substring pattern
```

## Practical Examples

### Real-World Caching with Global Functions
```php
// Cache expensive API calls
$weather_data = wp_cache_remember_by( 'weather_api', function () use ( $city ) {
	return wp_remote_get( "https://api.weather.com/current/{$city}" );
}, 15 * MINUTE_IN_SECONDS, $city );

// Cache database queries
$popular_posts = wp_cache_remember( 'popular_posts_today', function () {
	global $wpdb;

	return $wpdb->get_results( "
        SELECT p.*, COUNT(v.post_id) as view_count 
        FROM wp_posts p 
        LEFT JOIN wp_post_views v ON p.ID = v.post_id 
        WHERE p.post_status = 'publish' 
        GROUP BY p.ID 
        ORDER BY view_count DESC 
        LIMIT 10
    " );
}, HOUR_IN_SECONDS );

// Track page views
$new_views = wp_transient_increment( 'page_views_' . get_the_ID() );

// Feature flags
if ( wp_transient_toggle( 'beta_features_enabled' ) ) {
	// Show beta features
}
```

### Advanced Remember Pattern Usage
```php
// Time-based cache keys
$daily_stats = Cache::remember( 'daily_stats_' . date( 'Y-m-d' ), function () {
	return generate_daily_statistics();
}, DAY_IN_SECONDS );

// Conditional caching based on user type
$cache_key      = $user->is_premium() ? 'premium_dashboard_' . $user_id : 'basic_dashboard_' . $user_id;
$dashboard_data = Cache::remember( $cache_key, function () use ( $user ) {
	return generate_dashboard_data( $user );
}, HOUR_IN_SECONDS );

// Multi-parameter auto-generated keys
$search_results = Cache::remember_by( 'search_results', function () use ( $query, $filters, $page ) {
	return perform_complex_search( $query, $filters, $page );
}, 30 * MINUTE_IN_SECONDS, $query, $filters, $page );
```

### Numeric Counters and Analytics
```php
// Using global functions (recommended)
wp_transient_increment( 'api_requests_' . $user_id );
wp_transient_increment( 'downloads_' . $file_id, 1, WEEK_IN_SECONDS );

// Using class methods (for advanced control)
$new_count = Transient::increment_value( 'page_views', 5, DAY_IN_SECONDS );
$new_count = Transient::decrement_value( 'credits_remaining', 1, MONTH_IN_SECONDS );
```

### Type-Safe Data Retrieval
```php
// Ensure consistent data types
$user_id    = Transient::get_cast( 'current_user', 'int', 0 );
$settings   = Transient::get_cast( 'site_config', 'array', [] );
$is_enabled = Transient::get_cast( 'feature_flag', 'bool', false );

// Validate data types with helper methods
if ( Transient::is_type( 'user_preferences', 'array' ) ) {
	$prefs = Transient::get( 'user_preferences' );
	// Process array safely
}

// Size validation for performance
if ( ! Transient::is_large( 'api_response' ) ) {
	// Safe to process - not too large
	$data = Transient::get( 'api_response' );
}
```

### Cache Management and Cleanup
```php
// Plugin cleanup - remove all plugin caches
$deleted = Transients::delete_by_prefix( 'my_plugin_' );

// Pattern-based cleanup
$deleted = Transients::delete_by_pattern( '_temp', 'suffix' );
$deleted = Transients::delete_by_pattern( 'user_', 'substring' );

// Global functions create consistent prefixes for easy cleanup
wp_cache_remember_by( 'user_data', $callback, $expiration, $user_id );
wp_cache_remember_by( 'user_stats', $callback, $expiration, $user_id );

// Later, clean all user-related caches
Transients::delete_by_prefix( 'user_data_' );
Transients::delete_by_prefix( 'user_stats_' );
```

## API Reference

### Global Helper Functions

**Remember Pattern:**
- `wp_cache_remember( $key, $callback, $expiration )` - Get from cache or compute
- `wp_cache_remember_by( $prefix, $callback, $expiration, ...$args )` - Remember with auto-generated keys

**Common Operations:**
- `wp_transient_increment( $key, $amount, $expiration )` - Increment counter
- `wp_transient_toggle( $key, $expiration )` - Toggle boolean value

### Cache Class Methods (High-Level Patterns)

**Remember Pattern:**
- `remember( $key, $callback, $expiration )` - Get from cache or compute with callback
- `remember_by( $prefix, $callback, $expiration, ...$args )` - Remember with auto-generated keys

### Transient Class Methods (Low-Level Operations)

**Core Operations:**
- `exists( $transient )` - Check if transient exists
- `get( $transient )` - Get transient value
- `get_with_default( $transient, $default )` - Get with fallback
- `get_cast( $transient, $type, $default )` - Get with type casting
- `set( $transient, $value, $expiration )` - Set transient
- `delete( $transient )` - Delete transient

**Numeric Operations:**
- `increment_value( $transient, $amount, $expiration )` - Increment counter
- `decrement_value( $transient, $amount, $expiration )` - Decrement counter

**Utility Methods:**
- `get_type( $transient )` - Get value type
- `get_size( $transient )` - Get size in bytes
- `toggle( $transient, $expiration )` - Toggle boolean value
- `is_type( $transient, $type )` - Check if value is specific type
- `is_large( $transient, $size_limit )` - Check if transient exceeds size limit

### Transients Class Methods (Bulk Operations)

**Bulk Operations:**
- `exists( $names )` - Check multiple transients
- `get( $names )` - Get multiple transients
- `set( $transients, $expiration )` - Set multiple transients
- `delete( $names )` - Delete multiple transients

**Discovery:**
- `get_all( $include_values )` - Get all transients
- `get_by_prefix( $prefix, $include_values )` - Get by prefix

**Pattern Deletion:**
- `delete_by_pattern( $pattern, $type )` - Delete by pattern
- `delete_by_prefix( $prefix )` - Delete by prefix (convenience method)

## When to Use What

### Use Global Functions for:
- **Quick caching** without class imports
- **Theme development** where simplicity matters
- **Common operations** like remember, increment, toggle
- **WordPress-style coding** that feels native

```php
// WordPress-style - clean and simple
$data  = wp_cache_remember_by( 'user_stats', $callback, HOUR_IN_SECONDS, $user_id );
$views = wp_transient_increment( 'page_views_' . $post_id );
```

### Use Cache class for:
- **Advanced remember patterns** with custom keys
- **Plugin development** where you import classes anyway
- **Complex caching scenarios**

```php
// When you need custom cache keys or complex patterns
$data = Cache::remember( 'complex_key_' . $hash, $callback, $expiration );
```

### Use Transient class for:
- **Direct transient manipulation**
- **Type validation and size checking**
- **Advanced numeric operations**

```php
// When you need type safety and validation
if ( Transient::is_large( 'dataset' ) || ! Transient::is_type( 'config', 'array' ) ) {
	// Handle issues
}
```

### Use Transients class for:
- **Bulk operations** on multiple transients
- **Cache cleanup** and maintenance
- **Pattern-based deletion**

```php
// Administrative and cleanup operations
Transients::delete_by_prefix( 'temp_' );
$values = Transients::get( [ 'key1', 'key2', 'key3' ] );
```

## Supported Type Casting

The `get_cast()` method supports these types:
- `'int'` or `'integer'` - Cast to integer
- `'float'` or `'double'` - Cast to float
- `'bool'` or `'boolean'` - Cast to boolean
- `'array'` - Cast to array
- `'string'` - Cast to string

## Requirements

- PHP 7.4+
- WordPress 5.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/wp-transient-utils)
- [Issue Tracker](https://github.com/arraypress/wp-transient-utils/issues)