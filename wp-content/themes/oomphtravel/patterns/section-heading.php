<?php
/**
 * Title: Section heading
 * Slug: oomphtravel/section-heading
 * Categories: oomphtravel
 * Description: Eyebrow with its static contrail mark, then the Fraunces H2. Once per section.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

echo oomphtravel_section_heading( __( 'How it works', 'oomphtravel' ), __( 'Discover, design, depart.', 'oomphtravel' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
