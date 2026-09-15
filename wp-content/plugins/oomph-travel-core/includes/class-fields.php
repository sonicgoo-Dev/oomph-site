<?php
/**
 * Field access that works with or without a fields plugin.
 *
 * The record fields are defined in acf-json/ and edited through ACF Pro or
 * SCF (Eric's choice, docs/stage-4-fields-runbook.md). Both store a value
 * under the field name and, for a repeater, the row count under the name
 * with each cell at `{name}_{row}_{sub}`. Reading that layout directly means
 * the theme renders the same page inside wp-env (no fields plugin) as on
 * staging, and the seeder can write records the admin form shows straight
 * away.
 *
 * @package OomphTravel\Core
 */

declare( strict_types=1 );

namespace OomphTravel\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Fields {

	/** A scalar field's value, '' when unset. */
	public static function value( int $post_id, string $name ): string {
		$v = get_post_meta( $post_id, $name, true );
		return is_scalar( $v ) ? (string) $v : '';
	}

	/** A checkbox field's values as strings, [] when unset. */
	public static function choices( int $post_id, string $name ): array {
		$v = get_post_meta( $post_id, $name, true );
		if ( is_string( $v ) && '' !== $v ) {
			$v = maybe_unserialize( $v );
		}
		return is_array( $v ) ? array_values( array_map( 'strval', $v ) ) : array();
	}

	/**
	 * A repeater's rows, each keyed by sub-field name, [] when empty.
	 *
	 * @param string[] $subs Sub-field names.
	 * @return array<int,array<string,string>>
	 */
	public static function repeater( int $post_id, string $name, array $subs ): array {
		$count = (int) get_post_meta( $post_id, $name, true );
		$rows  = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$row = array();
			foreach ( $subs as $sub ) {
				$row[ $sub ] = self::value( $post_id, "{$name}_{$i}_{$sub}" );
			}
			if ( '' !== implode( '', $row ) ) {
				$rows[] = $row;
			}
		}
		return $rows;
	}

	/** Write a scalar the way the fields plugin does (value + field key). */
	public static function write( int $post_id, string $name, string $key, $value ): void {
		update_post_meta( $post_id, $name, $value );
		update_post_meta( $post_id, '_' . $name, $key );
	}

	/**
	 * Write a repeater the way the fields plugin does.
	 *
	 * @param array<int,array<string,mixed>> $rows     Rows keyed by sub-field name.
	 * @param array<string,string>           $sub_keys Sub-field name → field key.
	 */
	public static function write_repeater( int $post_id, string $name, string $key, array $rows, array $sub_keys ): void {
		$i = 0;
		foreach ( $rows as $row ) {
			foreach ( $sub_keys as $sub => $sub_key ) {
				self::write( $post_id, "{$name}_{$i}_{$sub}", $sub_key, $row[ $sub ] ?? '' );
			}
			++$i;
		}
		self::write( $post_id, $name, $key, (string) $i );
	}
}
