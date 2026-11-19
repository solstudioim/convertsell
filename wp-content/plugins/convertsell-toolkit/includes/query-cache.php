<?php
/** Bounded candidate caches; shopper-specific HTML and prices are never cached. */
defined( 'ABSPATH' ) || exit;

/** Invalidate review candidates on content, approval, rating or product changes. */
function pot_invalidate_review_candidates(): void {
	update_option( 'convertsell_review_cache_generation', wp_generate_uuid4(), false );
}
add_action( 'clean_comment_cache', 'pot_invalidate_review_candidates' );
add_action( 'added_comment_meta', 'pot_invalidate_review_candidates' );
add_action( 'updated_comment_meta', 'pot_invalidate_review_candidates' );
add_action( 'deleted_comment_meta', 'pot_invalidate_review_candidates' );
add_action( 'save_post_product', 'pot_invalidate_review_candidates' );

/** Cache IDs across requests, then re-read approved reviews and their current metadata. */
function pot_review_candidates( array $args ): array {
	$args['no_found_rows'] = true;
	$args['update_comment_meta_cache'] = true;
	$args['update_comment_post_cache'] = true;
	$key = 'convertsell_reviews_' . md5( wp_json_encode( array( $args, get_option( 'convertsell_review_cache_generation', '' ) ) ) );
	$cached = get_transient( $key );
	if ( is_array( $cached ) && isset( $cached['ids'] ) ) {
		if ( ! $cached['ids'] ) { return array(); }
		return get_comments( array(
			'comment__in' => $cached['ids'], 'orderby' => 'comment__in',
			'status' => 'approve', 'type' => 'review', 'parent' => 0,
			'number' => count( $cached['ids'] ), 'no_found_rows' => true,
			'update_comment_meta_cache' => true, 'update_comment_post_cache' => true,
		) );
	}
	$reviews = get_comments( $args );
	set_transient( $key, array( 'ids' => array_map( 'intval', wp_list_pluck( $reviews, 'comment_ID' ) ) ), MINUTE_IN_SECONDS );
	return $reviews;
}

/** Use WooCommerce's narrow SKU lookup table instead of joining arbitrary post metadata. */
function pot_sku_search_posts( array $args, string $term ): array {
	if ( strlen( $term ) < 2 || strlen( $term ) > 128 ) { return array(); }
	$filter = static function ( string $search, WP_Query $query ) use ( $term ): string {
		if ( ! $query->get( 'convertsell_sku_search' ) ) { return $search; }
		global $wpdb;
		return $wpdb->prepare(
			" AND {$wpdb->posts}.ID IN (SELECT product_id FROM {$wpdb->wc_product_meta_lookup} WHERE sku LIKE %s) AND {$wpdb->posts}.post_password = ''",
			'%' . $wpdb->esc_like( $term ) . '%'
		);
	};
	add_filter( 'posts_search', $filter, 10, 2 );
	try {
		$query = new WP_Query( array_merge( $args, array( 's' => $term, 'convertsell_sku_search' => true ) ) );
		return $query->posts;
	} finally {
		remove_filter( 'posts_search', $filter, 10 );
	}
}
