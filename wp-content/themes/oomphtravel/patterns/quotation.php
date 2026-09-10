<?php
/**
 * Title: Quotation
 * Slug: oomphtravel/quotation
 * Categories: oomphtravel
 * Description: A client story. Fraunces Light Italic on a 2px teal rule, attribution in muted slate. No stars, no photographs.
 *
 * Client quotes are never reworded (D38) and only the four verified reviews
 * may be used (D37); pages without a matching real story carry the bracketed
 * prompt below instead of written copy.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

echo oomphtravel_quotation( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
	'[Client story needed: one of the four verified reviews, word for word.]',
	'[Client name] · [trip] · [year]'
);
