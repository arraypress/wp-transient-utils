<?php
/**
 * Global Helper Functions for WordPress Transients
 *
 * Provides WordPress-style global functions for common caching patterns.
 * These functions wrap the ArrayPress Transients utilities for better developer experience.
 *
 * @package ArrayPress\TransientsUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

use ArrayPress\TransientsUtils\Cache;
use ArrayPress\TransientsUtils\Transient;

if ( ! function_exists( 'wp_cache_remember' ) ) {
	/**
	 * Get a value from cache. If it doesn't exist, compute it using the provided callback.
	 *
	 * This function implements the "remember" pattern - it will first try to get the value
	 * from cache, and if not found, execute the callback to compute the value and store it.
	 *
	 * @param string   $key        The cache key.
	 * @param callable $callback   The function to compute the value if not found in cache.
	 * @param int      $expiration Optional. Time until expiration in seconds. Default DAY_IN_SECONDS.
	 *
	 * @return mixed The cached or computed value.
	 *
	 * @since 1.0.0
	 *
	 * @example
	 * $popular_posts = wp_cache_remember('popular_posts', function() {
	 *     return expensive_database_query();
	 * }, HOUR_IN_SECONDS);
	 */
	function wp_cache_remember( string $key, callable $callback, int $expiration = DAY_IN_SECONDS ) {
		return Cache::remember( $key, $callback, $expiration );
	}
}

if ( ! function_exists( 'wp_cache_remember_by' ) ) {
	/**
	 * Remember a value with automatic cache key generation from prefix and arguments.
	 *
	 * This function automatically generates a unique cache key based on the prefix and
	 * provided arguments, then uses the remember pattern to get or compute the value.
	 *
	 * @param string   $prefix     Cache key prefix.
	 * @param callable $callback   The function to compute the value if not found in cache.
	 * @param int      $expiration Optional. Time until expiration in seconds. Default DAY_IN_SECONDS.
	 * @param mixed    ...$args    Arguments to include in the cache key generation.
	 *
	 * @return mixed The cached or computed value.
	 *
	 * @since 1.0.0
	 *
	 * @example
	 * $user_stats = wp_cache_remember_by('user_stats', function() use ($user_id) {
	 *     return calculate_user_statistics($user_id);
	 * }, HOUR_IN_SECONDS, $user_id);
	 */
	function wp_cache_remember_by( string $prefix, callable $callback, int $expiration = DAY_IN_SECONDS, ...$args ) {
		return Cache::remember_by( $prefix, $callback, $expiration, ...$args );
	}
}

if ( ! function_exists( 'wp_transient_increment' ) ) {
	/**
	 * Increment a numeric transient value.
	 *
	 * Increments the value of a transient by the specified amount. If the transient
	 * doesn't exist, it will be initialized to 0 before incrementing.
	 *
	 * @param string $key        The transient key.
	 * @param int    $amount     Optional. Amount to increment. Default 1.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default DAY_IN_SECONDS.
	 *
	 * @return int|false The new transient value on success, false on failure.
	 *
	 * @since 1.0.0
	 *
	 * @example
	 * // Increment page views
	 * $new_count = wp_transient_increment('page_views_123');
	 *
	 * // Increment by 5
	 * $new_count = wp_transient_increment('api_requests', 5, HOUR_IN_SECONDS);
	 */
	function wp_transient_increment( string $key, int $amount = 1, int $expiration = DAY_IN_SECONDS ) {
		return Transient::increment_value( $key, $amount, $expiration );
	}
}

if ( ! function_exists( 'wp_transient_toggle' ) ) {
	/**
	 * Toggle a boolean transient value.
	 *
	 * Toggles the boolean value of a transient. If the transient doesn't exist,
	 * it will be treated as false and toggled to true.
	 *
	 * @param string $key        The transient key.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default DAY_IN_SECONDS.
	 *
	 * @return bool|null New boolean value on success, null on failure.
	 *
	 * @since 1.0.0
	 *
	 * @example
	 * // Toggle maintenance mode
	 * $maintenance_on = wp_transient_toggle('maintenance_mode', HOUR_IN_SECONDS);
	 *
	 * // Toggle feature flag
	 * $feature_enabled = wp_transient_toggle('new_feature_enabled');
	 */
	function wp_transient_toggle( string $key, int $expiration = DAY_IN_SECONDS ): ?bool {
		return Transient::toggle( $key, $expiration );
	}
}