<?php
/**
 * Transient Utility Class
 *
 * Provides utility functions for working with individual WordPress transients,
 * focusing on core functionality with useful type casting and numeric operations.
 *
 * @package ArrayPress\TransientsUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\TransientsUtils;

/**
 * Transient Class
 *
 * Core operations for working with individual WordPress transients.
 */
class Transient {

	/**
	 * Check if a transient exists.
	 *
	 * @param string $transient Transient name.
	 *
	 * @return bool True if the transient exists, false otherwise.
	 */
	public static function exists( string $transient ): bool {
		return get_transient( $transient ) !== false;
	}

	/**
	 * Get a transient value.
	 *
	 * @param string $transient Transient name.
	 *
	 * @return mixed Transient value or false if not found.
	 */
	public static function get( string $transient ) {
		return get_transient( $transient );
	}

	/**
	 * Get transient with a default if not set.
	 *
	 * @param string $transient Transient name.
	 * @param mixed  $default   Default value to return if the transient does not exist.
	 *
	 * @return mixed The transient value or default.
	 */
	public static function get_with_default( string $transient, $default ) {
		$value = self::get( $transient );

		return $value !== false ? $value : $default;
	}

	/**
	 * Get transient value with type casting.
	 *
	 * @param string $transient Transient name.
	 * @param string $cast_type The type to cast to ('int', 'float', 'bool', 'array', 'string').
	 * @param mixed  $default   Default value to return if transient doesn't exist.
	 *
	 * @return mixed The transient value cast to the specified type, or default.
	 */
	public static function get_cast( string $transient, string $cast_type, $default = null ) {
		$value = self::get( $transient );

		if ( $value === false && $default !== null ) {
			return Utils::cast_value( $default, $cast_type );
		}

		return Utils::cast_value( $value, $cast_type );
	}

	/**
	 * Set a transient.
	 *
	 * @param string $transient  Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Time until expiration in seconds.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	public static function set( string $transient, $value, int $expiration = 0 ): bool {
		return set_transient( $transient, $value, $expiration );
	}

	/**
	 * Delete a transient.
	 *
	 * @param string $transient Transient name.
	 *
	 * @return bool True if successful, false otherwise.
	 */
	public static function delete( string $transient ): bool {
		return delete_transient( $transient );
	}

	// ========================================
	// Numeric Operations
	// ========================================

	/**
	 * Increment or decrement a numeric transient value.
	 *
	 * @param string $transient  Name of the transient to update.
	 * @param int    $amount     Amount to increment (positive) or decrement (negative).
	 * @param int    $expiration Time until expiration in seconds.
	 *
	 * @return int|bool The new transient value on success, false on failure.
	 */
	public static function increment_value( string $transient, int $amount = 1, int $expiration = 0 ) {
		$current_value = (int) self::get_with_default( $transient, 0 );
		$new_value     = $current_value + $amount;

		return self::set( $transient, $new_value, $expiration ) ? $new_value : false;
	}

	/**
	 * Decrement a numeric transient value.
	 *
	 * @param string $transient  Name of the transient to update.
	 * @param int    $amount     Amount to decrement (positive number).
	 * @param int    $expiration Time until expiration in seconds.
	 *
	 * @return int|bool The new transient value on success, false on failure.
	 */
	public static function decrement_value( string $transient, int $amount = 1, int $expiration = 0 ) {
		$current_value = (int) self::get_with_default( $transient, 0 );
		$new_value     = $current_value - abs( $amount );

		return self::set( $transient, $new_value, $expiration ) ? $new_value : false;
	}

	// ========================================
	// Utility Methods
	// ========================================

	/**
	 * Get the type of a transient's value.
	 *
	 * @param string $transient Transient name.
	 *
	 * @return string|null The type of the transient value or null if transient doesn't exist.
	 */
	public static function get_type( string $transient ): ?string {
		$value = self::get( $transient );

		return $value !== false ? gettype( $value ) : null;
	}

	/**
	 * Get the size of a transient in bytes.
	 *
	 * @param string $transient Transient name.
	 *
	 * @return int Size in bytes, 0 if transient doesn't exist.
	 */
	public static function get_size( string $transient ): int {
		$value = self::get( $transient );

		return $value !== false ? strlen( serialize( $value ) ) : 0;
	}

	/**
	 * Toggle a boolean transient value.
	 *
	 * @param string $transient  Transient name.
	 * @param int    $expiration Time until expiration in seconds.
	 *
	 * @return bool|null New value on success, null on failure.
	 */
	public static function toggle( string $transient, int $expiration = 0 ): ?bool {
		$value = self::get_cast( $transient, 'bool', false );

		return self::set( $transient, ! $value, $expiration ) ? ! $value : null;
	}

	/**
	 * Check if a transient's value is of a specific type.
	 *
	 * @param string $transient Transient name.
	 * @param string $type      Expected type ('string', 'array', 'integer', 'boolean', etc.).
	 *
	 * @return bool True if the transient value matches the expected type, false otherwise.
	 */
	public static function is_type( string $transient, string $type ): bool {
		$actual_type = self::get_type( $transient );

		return $actual_type !== null && $actual_type === $type;
	}

	/**
	 * Check if a transient is large (exceeds specified size).
	 *
	 * @param string $transient  Transient name.
	 * @param int    $size_limit Size limit in bytes. Default 1MB (1048576 bytes).
	 *
	 * @return bool True if the transient exceeds the size limit, false otherwise.
	 */
	public static function is_large( string $transient, int $size_limit = 1048576 ): bool {
		return self::get_size( $transient ) > $size_limit;
	}

}