<?php
/** Reversible query semantics/cache checks against an isolated WordPress site. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) { throw new RuntimeException( 'Local fixture only.' ); }
$expect = static function ( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } };
$products = array(); $comments = array(); $categories = array(); $queries = array();
$observe = static function ( $sql ) use ( &$queries ) { $queries[] = $sql; return $sql; };
add_filter( 'query', $observe );
try {
	foreach ( array( 'alpha', 'beta' ) as $suffix ) {
		$term = wp_insert_term( 'Query fixture ' . wp_generate_uuid4(), 'product_cat' );
		$categories[] = $term['term_id'];
		$p = new WC_Product_Simple(); $p->set_name( 'Query fixture ' . $suffix ); $p->set_status( 'publish' ); $p->set_regular_price( '10' ); $p->set_sku( 'QPERF-' . wp_generate_uuid4() . '-MATCH' ); $p->set_category_ids( array( $term['term_id'] ) ); $products[] = $p->save();
	}
	$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 6, 'no_found_rows' => true, 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $categories[0] ) ) );
	$before_filters = isset( $GLOBALS['wp_filter']['posts_search'] ) ? $GLOBALS['wp_filter']['posts_search']->callbacks : array();
	$queries = array();
	$found = pot_sku_search_posts( $args, '-MATCH' );
	$expect( array( $products[0] ) === array_map( 'intval', wp_list_pluck( $found, 'ID' ) ), 'Partial SKU search must honor category scope.' );
	$expect( (bool) array_filter( $queries, static function ( $sql ) { return false !== strpos( $sql, 'wc_product_meta_lookup' ); } ), 'SKU search must use the WooCommerce lookup table.' );
	$expect( ! array_filter( $queries, static function ( $sql ) { global $wpdb; return (bool) preg_match( '/JOIN\s+' . preg_quote( $wpdb->postmeta, '/' ) . '\b/i', $sql ); } ), 'SKU search must not join post metadata.' );
	$expect( array() === pot_sku_search_posts( $args, "' OR 1=1 --" ), 'Search input must remain a literal pattern.' );
	$expect( array() === pot_sku_search_posts( $args, str_repeat( 'x', 129 ) ), 'Oversized input must not execute catalog searches.' );
	$after_filters = isset( $GLOBALS['wp_filter']['posts_search'] ) ? $GLOBALS['wp_filter']['posts_search']->callbacks : array();
	$expect( $before_filters === $after_filters, 'Temporary SKU filter must be removed.' );
	foreach ( array( 5, 2 ) as $rating ) {
		$id = wp_insert_comment( array( 'comment_post_ID' => $products[0], 'comment_type' => 'review', 'comment_approved' => 1, 'comment_content' => 'Query fixture review', 'comment_author' => 'Fixture' ) );
		$comments[] = $id; update_comment_meta( $id, 'rating', $rating );
	}
	$config = pot_reviews_config( array( 'source' => 'products', 'products' => (string) $products[0], 'min_rating' => '4' ) );
	$queries = array();
	$cold = pot_reviews_query( $config );
	$cold_rating_joins = count( array_filter( $queries, static function ( $sql ) { global $wpdb; return (bool) preg_match( '/JOIN\s+' . preg_quote( $wpdb->commentmeta, '/' ) . '\b/i', $sql ); } ) );
	$expect( $cold_rating_joins > 0, 'Cold selection must apply the rating filter in SQL before the result limit.' );
	$expect( array( $comments[0] ) === array_map( 'intval', wp_list_pluck( $cold, 'comment_ID' ) ), 'Rating filter must preserve matching review selection.' );
	// Clear request-local object caches: the second call must use persistent transient IDs.
	wp_cache_flush(); $queries = array();
	$warm = pot_reviews_query( $config );
	$expect( count( $warm ) === 1, 'Warm candidate cache must return the approved review.' );
	$expect( ! array_filter( $queries, static function ( $sql ) { global $wpdb; return (bool) preg_match( '/JOIN\s+' . preg_quote( $wpdb->commentmeta, '/' ) . '\b/i', $sql ); } ), 'Warm cache must avoid rating metadata joins, even without a persistent object cache.' );
	update_comment_meta( $comments[0], 'rating', 1 );
	$expect( array() === pot_reviews_query( $config ), 'Rating edits must invalidate cached membership.' );
	update_comment_meta( $comments[0], 'rating', 5 );
	$expect( count( pot_reviews_query( $config ) ) === 1, 'Rating recovery must invalidate cached empty results.' );
	wp_set_comment_status( $comments[0], 'hold' );
	$expect( array() === pot_reviews_query( $config ), 'Unapproved reviews must disappear immediately.' );
	wp_set_comment_status( $comments[0], 'approve' );
	wp_update_post( array( 'ID' => $products[0], 'post_status' => 'private' ) );
	$expect( array() === pot_reviews_query( $config ), 'Product visibility changes must not expose cached reviews.' );
	WP_CLI::success( 'Partial SKU/category/SQL safety, no postmeta join, cached review membership, zero warm rating joins, rating/approval/visibility invalidation passed.' );
} finally {
	remove_filter( 'query', $observe );
	foreach ( $comments as $id ) { wp_delete_comment( $id, true ); }
	foreach ( $products as $id ) { wp_delete_post( $id, true ); }
	foreach ( $categories as $id ) { wp_delete_term( $id, 'product_cat' ); }
}
