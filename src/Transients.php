<?php
/**
 * Transients Utility Class
 *
 * Provides utility functions for working with multiple WordPress transients,
 * including bulk operations, pattern-based deletion, and system-wide management.
 *
 * @package ArrayPress\TransientsUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\TransientsUtils;

/**
 * Transients Class
 *
 * Operations for working with multiple WordPress transients.
 */
class Transients {

	/**
	 * Check if multiple transients exist.
	 *
	 * @param array $names Array of transient names to check.
	 *
	 * @return array Array of existing transient names.
	 */
	public static function exists( array $names ): array {
		if ( empty( $names ) ) {
			return [];
		}

		return array_filter( $names, function ( $name ) {
			return Transient::exists( $name );
		} );
	}

	/**
	 * Get multiple transients at once.
	 *
	 * @param array $names Array of transient names to retrieve.
	 *
	 * @return array Array of transients with names as keys and their values.
	 */
	public static function get( array $names ): array {
		if ( empty( $names ) ) {
			return [];
		}

		$results = [];
		foreach ( $names as $name ) {
			$value = Transient::get( $name );
			if ( $value !== false ) {
				$results[ $name ] = $value;
			}
		}

		return $results;
	}

	/**
	 * Set multiple transients at once.
	 *
	 * @param array $transients Array of transient names and their values.
	 * @param int   $expiration Time until expiration in seconds.
	 *
	 * @return array Array of successfully set transient names.
	 */
	public static function set( array $transients, int $expiration = 0 ): array {
		if ( empty( $transients ) ) {
			return [];
		}

		$set = [];
		foreach ( $transients as $name => $value ) {
			if ( Transient::set( $name, $value, $expiration ) ) {
				$set[] = $name;
			}
		}

		return $set;
	}

	/**
	 * Delete multiple transients.
	 *
	 * @param array $names Array of transient names to delete.
	 *
	 * @return int Number of transients successfully deleted.
	 */
	public static function delete( array $names ): int {
		if ( empty( $names ) ) {
			return 0;
		}

		return array_reduce( $names, function ( $count, $name ) {
			return $count + ( Transient::delete( $name ) ? 1 : 0 );
		}, 0 );
	}

	/**
	 * Get all transients.
	 *
	 * @param bool $include_values Whether to include the transient values. Default true.
	 *
	 * @return array Array of all transients.
	 */
	public static function get_all( bool $include_values = true ): array {
		global $wpdb;

		$query = "SELECT option_name 
              FROM $wpdb->options 
              WHERE option_name LIKE '_transient_%'
              AND option_name NOT LIKE '_transient_timeout_%'";

		$transients = $wpdb->get_col( $query );

		if ( ! $include_values ) {
			return array_map( function ( $transient ) {
				return str_replace( '_transient_', '', $transient );
			}, $transients );
		}

		$result = [];
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			$value          = Transient::get( $transient_name );
			if ( $value !== false ) {
				$result[ $transient_name ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Get all transients with a specified prefix.
	 *
	 * @param string $prefix         Prefix to search for in transient keys.
	 * @param bool   $include_values Whether to include the transient values. Default true.
	 *
	 * @return array Array of transients with the specified prefix.
	 */
	public static function get_by_prefix( string $prefix, bool $include_values = true ): array {
		global $wpdb;

		$wildcard   = $wpdb->esc_like( '_transient_' . $prefix ) . '%';
		$transients = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$wildcard
		) );

		if ( ! $include_values ) {
			return array_map( function ( $transient ) {
				return str_replace( '_transient_', '', $transient );
			}, $transients );
		}

		$result = [];
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			$value          = Transient::get( $transient_name );
			if ( $value !== false ) {
				$result[ $transient_name ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Delete transients based on a pattern.
	 *
	 * @param string $pattern The pattern to match against transient names.
	 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
	 *
	 * @return int The number of transients deleted.
	 */
	public static function delete_by_pattern( string $pattern, string $type = 'exact' ): int {
		global $wpdb;

		$sql_pattern = self::generate_like_pattern( $pattern, $type, '_transient_' );
		$transients  = $wpdb->get_col( $wpdb->prepare(
			"SELECT option_name FROM $wpdb->options 
             WHERE option_name LIKE %s 
             AND option_name LIKE '_transient_%'",
			$sql_pattern
		) );

		$count = 0;
		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			if ( Transient::delete( $transient_name ) ) {
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Delete all transients that are prefixed with a specific string.
	 *
	 * @param string $prefix The prefix to search for.
	 *
	 * @return int The number of transients deleted.
	 */
	public static function delete_by_prefix( string $prefix ): int {
		return self::delete_by_pattern( $prefix, 'prefix' );
	}

	// ========================================
	// Private Helper Methods
	// ========================================

	/**
	 * Generate a general LIKE pattern for database queries.
	 *
	 * @param string $pattern The pattern to match.
	 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
	 * @param string $prefix  Optional prefix to add before the pattern. Default empty.
	 *
	 * @return string The SQL LIKE pattern.
	 */
	private static function generate_like_pattern( string $pattern, string $type, string $prefix = '' ): string {
		global $wpdb;

		$escaped_pattern = $wpdb->esc_like( $pattern );
		$full_prefix     = $prefix ? $wpdb->esc_like( $prefix ) : '';

		switch ( $type ) {
			case 'prefix':
				return $full_prefix . $escaped_pattern . '%';
			case 'suffix':
				return $full_prefix . '%' . $escaped_pattern;
			case 'substring':
				return $full_prefix . '%' . $escaped_pattern . '%';
			case 'exact':
			default:
				return $full_prefix . $escaped_pattern;
		}
	}

}