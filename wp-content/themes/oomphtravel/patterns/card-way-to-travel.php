<?php
/**
 * Title: Card / Way to travel row
 * Slug: oomphtravel/card-way-to-travel
 * Categories: oomphtravel
 * Description: Four way-to-travel cards: photo above, Fraunces title, two lines of body, one underlined link.
 *
 * The four from plan §6.1.4; one-line bodies are the plan's own definitions
 * (D01, D04, D06, §6.1.5).
 *
 * Each card's photo is the tall (3:4) rendition of the same page's hero
 * (assets/img/{key}-hero-tall-*.webp, see oomphtravel_way_hero_sources()
 * and the tours index hero), so the card a visitor clicks shows the picture
 * the page opens with. The 4:5 box crops a little off the top and bottom.
 *
 * @package OomphTravel
 */

declare( strict_types = 1 );

/**
 * The tall rendition set for one hero key, as card image_* keys.
 *
 * @param string $key custom | tours | resorts | multigen.
 * @param string $alt The photograph, described.
 * @return array<string,mixed>
 */
$ot_way_photo = static function ( string $key, string $alt ): array {
	$img = OOMPHTRAVEL_THEME_URI . 'assets/img/' . $key . '-hero-tall';
	return array(
		'image_url'    => $img . '-720.webp',
		'image_srcset' => $img . '-480.webp 480w, ' . $img . '-720.webp 720w',
		'image_sizes'  => '(min-width: 1024px) 300px, (min-width: 768px) 50vw, 100vw',
		'image_width'  => 720,
		'image_height' => 960,
		'image_alt'    => $alt,
	);
};

$ot_cards = array(
	array(
		'title' => __( 'Custom journeys', 'oomphtravel' ),
		'body'  => __( 'Independent, hand-planned trips by destination.', 'oomphtravel' ),
		'url'   => home_url( '/custom-journeys/' ),
	) + $ot_way_photo( 'custom', __( 'Florence at sunrise, from the air: the Duomo above the rooftops and the hills beyond.', 'oomphtravel' ) ),
	array(
		'title' => __( 'Escorted tours', 'oomphtravel' ),
		'body'  => __( 'Operator departures I sell, with my fit notes.', 'oomphtravel' ),
		'url'   => home_url( '/escorted-tours/' ),
	) + $ot_way_photo( 'tours', __( 'Dubrovnik’s old town from above: the walls, the harbour and the red roofs against the Adriatic.', 'oomphtravel' ) ),
	array(
		'title' => __( 'Resorts & villas', 'oomphtravel' ),
		'body'  => __( 'Resort stays, villas and private homes, and the hotels inside custom trips.', 'oomphtravel' ),
		'url'   => home_url( '/resorts-and-villas/' ),
	) + $ot_way_photo( 'resorts', __( 'A hotel terrace pool above the bay in Old San Juan, Puerto Rico.', 'oomphtravel' ) ),
	array(
		'title' => __( 'Multi-generational trips', 'oomphtravel' ),
		'body'  => __( 'Families across three generations.', 'oomphtravel' ),
		'url'   => home_url( '/multi-generational-travel-planning/' ),
	) + $ot_way_photo( 'multigen', __( 'Sitges, on the Catalan coast: the church of Sant Bartomeu above the beach in late light.', 'oomphtravel' ) ),
);
?>
<div class="ot-container">
	<div class="ot-grid ot-grid--4">
		<?php foreach ( $ot_cards as $ot_i => $ot_card ) : ?>
			<?php echo oomphtravel_card_way( $ot_card, 0 === $ot_i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		<?php endforeach; ?>
	</div>
</div>
