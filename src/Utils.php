<?php
/**
 * Utilities Class
 *
 * Provides general utility functions that can be shared across different components
 * of the WordPress utilities library.
 *
 * @package ArrayPress\TransientsUtils
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\TransientsUtils;

/**
 * Utils Class
 *
 * General utility functions and helpers.
 */
class Utils {

	/**
	 * Generate a general LIKE pattern for database queries.
	 *
	 * @param string $pattern The pattern to match.
	 * @param string $type    The type of pattern matching: 'prefix', 'suffix', 'substring', or 'exact'.
	 * @param string $prefix  Optional prefix to add before the pattern. Default empty.
	 *
	 * @return string The SQL LIKE pattern.
	 */
	public static function generate_like_pattern( string $pattern, string $type, string $prefix = '' ): string {
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

	/**
	 * Cast a value to a specific type.
	 *
	 * @param mixed  $value Value to cast.
	 * @param string $type  Type to cast to.
	 *
	 * @return mixed Casted value.
	 */
	public static function cast_value( $value, string $type ) {
		switch ( strtolower( $type ) ) {
			case 'int':
			case 'integer':
				return (int) $value;
			case 'float':
			case 'double':
				return (float) $value;
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'array':
				return (array) $value;
			case 'string':
				return (string) $value;
			default:
				return $value;
		}
	}

}