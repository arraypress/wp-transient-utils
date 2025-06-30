<?php
/**
 * Cache Utility Class
 *
 * Provides the essential "remember" caching pattern for WordPress.
 * Built on top of the lean Transient utilities.
 *
 * @package ArrayPress\TransientsUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\TransientsUtils;

/**
 * Cache Class
 *
 * Essential "remember" caching pattern.
 */
class Cache {

	/**
	 * Default cache expiration time in seconds.
	 */
	const DEFAULT_EXPIRATION = DAY_IN_SECONDS;

	/**
	 * Get a value from cache. If it doesn't exist, compute it using the provided callback.
	 *
	 * @param string   $key        The cache key.
	 * @param callable $callback   The function to compute the value if not found in cache.
	 * @param int      $expiration Optional. Time until expiration in seconds. Default is DEFAULT_EXPIRATION.
	 *
	 * @return mixed The cached or computed value.
	 */
	public static function remember( string $key, callable $callback, int $expiration = self::DEFAULT_EXPIRATION ) {
		$cached_value = Transient::get( $key );

		if ( $cached_value !== false ) {
			return $cached_value;
		}

		$value = $callback();

		// Cache the value even if it's null
		Transient::set( $key, $value, $expiration );

		return $value;
	}

	/**
	 * Remember a value with automatic cache key generation from prefix and arguments.
	 *
	 * @param string   $prefix     Cache key prefix.
	 * @param callable $callback   The function to compute the value if not found in cache.
	 * @param int      $expiration Optional. Time until expiration in seconds. Default is DEFAULT_EXPIRATION.
	 * @param mixed    ...$args    Arguments to include in the cache key generation.
	 *
	 * @return mixed The cached or computed value.
	 */
	public static function remember_by( string $prefix, callable $callback, int $expiration = self::DEFAULT_EXPIRATION, ...$args ) {
		$prefix = strtolower( trim( $prefix ) );

		if ( empty( $args ) ) {
			$key = $prefix;
		} else {
			$key = $prefix . '_' . md5( serialize( $args ) );
		}

		return self::remember( $key, $callback, $expiration );
	}

}