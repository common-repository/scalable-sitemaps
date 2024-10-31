<?php
/*
Plugin Name: Scalable Sitemaps
Plugin URI: http://rcollier.me/software/scalable-sitemaps/
Description: Provides fast, clean, efficient xml-compliant sitemaps for your website.
Version: 1.1.3
Tested up to: 4.2.2
Author: Rich Collier
Author URI: http://rcollier.me
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: scalable-sitemaps
*/

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) ) 
	die( 'Cheatin\', huh?' );

/**
 * Scalable sitemaps class
 */
class Scalable_Sitemaps {
	
	/**
	 * Holder for the instance object
	 *
	 * @var object
	 * @access public
	 * @static
	 */
	static $instance = null;

	/**
	 * Holder for the current request type
	 *
	 * @var string
	 * @access private
	 */
	private $current_request = null;
	
	/**
	 * Class constructor
	 */
	function __construct() {
		// Hook our init function in at WordPress init
		add_action( 'init', array( $this, 'action_init' ) );
	}

	/**
	 * Singleton init
	 *
	 * @uses self::$instance
	 *
	 * @return object Static instance of this class
	 */
	static function get_instance() {
		if ( null === self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
	
	/**
	 * Action run at WP init hook
	 *
	 * @uses sanitize_text_field()
	 * @uses $_SERVER
	 * @uses $this->sitemap_index_style()
	 * @uses $this->sitemap_child_style()
	 * @uses wp_safe_redirect()
	 * @uses home_url()
	 * @uses absint()
	 * @uses $this->sitemap_index()
	 * @uses $this->sitemap_pages()
	 * @uses $this->sitemap_news()
	 * @uses $this->sitemap_child()
	 * @uses apply_filters()
	 */
	function action_init() {
		// Get the request URI
		$request = sanitize_text_field( basename( $_SERVER['REQUEST_URI'] ) );
		
		// Index sitemap stylesheet
		if ( 'sitemap-index-style.xsl' == $request ) {
			$this->sitemap_index_style();
		}
		
		// Child sitemap stylesheet
		if ( 'sitemap-child-style.xsl' == $request ) {
			$this->sitemap_child_style();
		}

		// Redirect old url sitemap index to new url
		if ( $request == 'sitemap-xml' || $request == 'sitemap-index-xml' ) {
			wp_safe_redirect( home_url( '/sitemap.xml' ) );
			die();
		}

		// Redirect old url pages sitemap to new url
		if ( 'sitemap-pages-xml' == $request ) {
			wp_safe_redirect( home_url( '/sitemap-pages.xml' ) );
			die();
		}

		// Redirect old url news sitemap to new url
		if ( 'sitemap-news-xml' == $request ) {
			wp_safe_redirect( home_url( '/sitemap-news.xml' ) );
			die();
		}

		// Redirect old child sitemaps to new url
		if ( strpos( $request, 'sitemap-' ) !== false && strpos( $request, '-xml' ) !== false ) {
			$uri_parts = explode( '-', $request );
			wp_safe_redirect( home_url( '/sitemap-' . absint( $uri_parts[1] ) . '.xml' ) );
			die();
		}

		// Index sitemap
		if ( $request == 'sitemap.xml' || $request == 'sitemap-index.xml' ) {
			$this->sitemap_index();
		}
		
		// Pages and categories sitemap
		if ( 'sitemap-pages.xml' == $request ) {
			$this->sitemap_pages();
		}
		
		// News sitemap
		if ( 'sitemap-news.xml' == $request && false !== apply_filters( 'scalable_sitemaps_news_category', 'news' ) ) {
			$this->sitemap_news();
		}

		// Tags sitemap
		if ( 'sitemap-tags.xml' == $request && false !== apply_filters( 'scalable_sitemaps_tags', true ) ) {
			$this->sitemap_tags();
		}

		// Categories sitemap
		if ( 'sitemap-categories.xml' == $request ) {
			$this->sitemap_categories();
		}

		// Custom taxonomy terms sitemap
		$taxonomies = apply_filters( 'scalable_sitemaps_custom_taxonomies', array() );
		if ( 'sitemap-taxonomies.xml' == $request && is_array( $taxonomies ) && count( $taxonomies ) ) {
			$this->sitemap_taxonomies();
		}

		// Users sitemap
		if ( 'sitemap-users.xml' == $request ) {
			$this->sitemap_users();
		}

		// Child sitemap
		if ( strpos( $request, 'sitemap-' ) !== false && strpos( $request, '.xml' ) !== false ) {
			$date = str_replace( array( 'sitemap-', '.xml' ), '', $request );
			$datestamp = substr( $date, 0, 4 ) . '-' . substr( $date, 4, 2 ) . '-' . substr( $date, 6, 2 );
			$this->sitemap_child( $datestamp );
		}
	}
	
	/**
	 * Sends content type header and prints the sitemap header
	 * 
	 * @uses header()
	 * 
	 * @return null
	 */
	function sitemap_header() {
		header( 'Content-type: application/xml' );
		
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	}
	
	/**
	 * Sends content type header and prints XSL for sitemap index
	 *
	 * @uses $this->current_request
	 * @uses header()
	 */
	function sitemap_index_style() {
		$this->current_request = 'sitemap_index_style';

		header( 'Content-type: application/xml' );

		echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
		echo '<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">' . "\n";
		echo "\t" . '<body style="font-family:Arial;font-size:12pt;background-color:#eee;padding-left:16px;">' . "\n";
		echo "\t\t" . '<h1>Sitemap Index</h1>' . "\n";
		echo "\t\t" . '<xsl:for-each select="sitemapindex/sitemap">' . "\n";
		echo "\t\t\t\t" . '<div style="margin-bottom:10px;">' . "\n";
		echo "\t\t\t\t\t" . '<div><a href="{loc}"><xsl:value-of select="loc"/></a></div>' . "\n";
		echo "\t\t\t\t\t" . '<div>Lastmod <xsl:value-of select="lastmod"/></div>' . "\n";
		echo "\t\t\t\t" . '</div>' . "\n";
		echo "\t\t" . '</xsl:for-each>' . "\n";
		echo "\t" . '</body>' . "\n";
		echo '</html>';

		die();
	}
	
	/**
	 * Sends content type header and prints XSL for sitemap child
	 *
	 * @uses $this->current_request
	 * @uses header()
	 */
	function sitemap_child_style() {
		$this->current_request = 'sitemap_child_style';

		header( 'Content-type: application/xml' );

		echo '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n";
		echo '<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns="http://www.w3.org/1999/xhtml">' . "\n";
		echo "\t" . '<body style="font-family:Arial;font-size:12pt;background-color:#eee;padding-left:16px;">' . "\n";
		echo "\t\t" . '<h1>Sitemap</h1>' . "\n";
		echo "\t\t" . '<xsl:for-each select="urlset/url">' . "\n";
		echo "\t\t\t\t" . '<div style="margin-bottom:10px;">' . "\n";
		echo "\t\t\t\t\t" . '<div style="font-weight:bold;"><a href="{loc}"><xsl:value-of select="loc"/></a></div>' . "\n";
		echo "\t\t\t\t\t" . '<div>Lastmod <xsl:value-of select="lastmod"/></div>' . "\n";
		echo "\t\t\t\t\t" . '<div>Changefreq <xsl:value-of select="changefreq"/></div>' . "\n";
		echo "\t\t\t\t\t" . '<div>Priority <xsl:value-of select="priority"/></div>' . "\n";
		echo "\t\t\t\t" . '</div>' . "\n";
		echo "\t\t" . '</xsl:for-each>' . "\n";
		echo "\t" . '</body>' . "\n";
		echo '</html>';

		die();
	}
	
	/**
	 * Prints out the master/index sitemap
	 *
	 * @uses $this->current_request
	 * @uses esc_url()
	 * @uses home_url()
	 * @uses esc_html()
	 * @uses $this->get_all_dates()
	 * @uses $this->sitemap_header()
	 * @uses absint()
	 */
	function sitemap_index() {
		$this->current_request = 'sitemap_index';
		
		$this->sitemap_header();
		
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		// Print out the pages sitemap reference
		echo "\t<sitemap>\n";
		echo "\t\t<loc>" . esc_url( home_url( '/sitemap-pages.xml' ) ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( date( 'Y-m-d' ) . 'T23:59:00+00:00' ) . "</lastmod>\n";
		echo "\t</sitemap>\n";

		// Print out the tags sitemap reference
		if ( false !== apply_filters( 'scalable_sitemaps_tags', true ) ) {
			echo "\t<sitemap>\n";
			echo "\t\t<loc>" . esc_url( home_url( '/sitemap-tags.xml' ) ) . "</loc>\n";
			echo "\t\t<lastmod>" . esc_html( date( 'Y-m-d' ) . 'T23:59:00+00:00' ) . "</lastmod>\n";
			echo "\t</sitemap>\n";
		}

		// Print out the categories sitemap reference
		echo "\t<sitemap>\n";
		echo "\t\t<loc>" . esc_url( home_url( '/sitemap-categories.xml' ) ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( date( 'Y-m-d' ) . 'T23:59:00+00:00' ) . "</lastmod>\n";
		echo "\t</sitemap>\n";

		// Print out the users sitemap reference
		echo "\t<sitemap>\n";
		echo "\t\t<loc>" . esc_url( home_url( '/sitemap-users.xml' ) ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( date( 'Y-m-d' ) . 'T23:59:00+00:00' ) . "</lastmod>\n";
		echo "\t</sitemap>\n";

		// Print out the taxonomies sitemap reference if needed
		$taxonomies = apply_filters( 'scalable_sitemaps_custom_taxonomies', array() );
		if ( is_array( $taxonomies ) && count( $taxonomies ) ) {
			echo "\t<sitemap>\n";
			echo "\t\t<loc>" . esc_url( home_url( '/sitemap-taxonomies.xml' ) ) . "</loc>\n";
			echo "\t\t<lastmod>" . esc_html( date( 'Y-m-d' ) . 'T23:59:00+00:00' ) . "</lastmod>\n";
			echo "\t</sitemap>\n";
		}

		// Print out the news sitemap reference if needed
		if ( false !== apply_filters( 'scalable_sitemaps_news_category', 'news' ) ) {
			echo "\t<sitemap>\n";
			echo "\t\t<loc>" . esc_url( home_url( '/sitemap-news.xml' ) ) . "</loc>\n";
			echo "\t\t<lastmod>" . esc_html( date( 'Y-m-d' ) . 'T23:59:00+00:00' ) . "</lastmod>\n";
			echo "\t</sitemap>\n";
		}
		
		// Print out a reference to each date's sitemap
		foreach ( $this->get_all_dates() as $dates ) {
			echo "\t<sitemap>\n";
			echo "\t\t<loc>" . esc_url( home_url( '/sitemap-' . absint( $dates[1] ) . '.xml' ) ) . "</loc>\n";
			echo "\t\t<lastmod>" . esc_html( $dates[0] . 'T23:59:00+00:00' ) . "</lastmod>\n";
			echo "\t</sitemap>\n";
		}
		
		echo "</sitemapindex>";

		die();
	}

	/**
	 * Prints out a sitemap with tags
	 *
	 * @uses $this->current_request
	 * @uses $this->sitemap_header()
	 * @uses apply_filters
	 * @uses get_tags()
	 * @uses get_tag_link()
	 * @uses esc_url()
	 * @uses esc_html()
	 */	
	function sitemap_tags() {
		$this->current_request = 'sitemap_tags';

		$this->sitemap_header();

		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		// Get all tags and allow themes to filter which tags will be included
		$tags = apply_filters( 'scalable_sitemaps_tags', get_tags() );

		// Loop through tags and print references to each
		if ( is_array( $tags ) ) {
			foreach ( $tags as $tag ) {
				echo "\t<url>\n";
				echo "\t\t<loc>" . esc_url( get_tag_link( $tag->term_id ) ) . "</loc>\n";
				echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', date( 'Y-m-d H:i:s', time() - ( 60 * 60 * 24 * 2 ) ) ) . '+00:00' ) . "</lastmod>\n";
				echo "\t\t<priority>0.7</priority>\n";
				echo "\t</url>\n";
			}
		}

		echo "</urlset>";

		die();
	}

	/**
	 * Prints out a sitemap with categories
	 *
	 * @uses $this->current_request
	 * @uses $this->sitemap_header()
	 * @uses apply_filters
	 * @uses get_categories()
	 * @uses get_category_link()
	 * @uses esc_url()
	 * @uses esc_html()
	 */	
	function sitemap_categories() {
		$this->current_request = 'sitemap_categories';

		$this->sitemap_header();

		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		// Get all categories and allow themes to filter which categories will be included
		$categories = apply_filters( 'scalable_sitemaps_categories', get_categories() );
		
		// Loop through categories and print out references to each
		if ( is_array( $categories ) ) {
			foreach ( $categories as $category ) {
				echo "\t<url>\n";
				echo "\t\t<loc>" . esc_url( get_category_link( $category->cat_ID ) ) . "</loc>\n";
				echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', date( 'Y-m-d H:i:s', time() - ( 60 * 60 * 24 * 2 ) ) ) . '+00:00' ) . "</lastmod>\n";
				echo "\t\t<priority>0.7</priority>\n";
				echo "\t</url>\n";
			}
		}

		echo "</urlset>";

		die();
	}

	/**
	 * Prints out a sitemap with author posts urls
	 *
	 * @uses $this->current_request
	 * @uses $this->sitemap_header()
	 * @uses apply_filters
	 * @uses get_users()
	 * @uses get_author_posts_url()
	 * @uses esc_url()
	 * @uses esc_html()
	 */
	function sitemap_users() {
		$this->current_request = 'sitemap_users';

		$this->sitemap_header();

		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		// Get all users and allow themes to filter which users will be included
		$users = apply_filters( 'scalable_sitemaps_users', get_users() );
		
		// Loop through users and print out references to each
		if ( is_array( $users ) ) {
			foreach ( $users as $user ) {
				echo "\t<url>\n";
				echo "\t\t<loc>" . esc_url( get_author_posts_url( $user->ID ) ) . "</loc>\n";
				echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', date( 'Y-m-d H:i:s', time() - ( 60 * 60 * 24 * 2 ) ) ) . '+00:00' ) . "</lastmod>\n";
				echo "\t\t<priority>0.6</priority>\n";
				echo "\t</url>\n";
			}
		}

		echo "</urlset>";

		die();
	}

	/**
	 * Prints out a sitemap with term links for configured custom taxonomies
	 *
	 * @uses $this->current_request
	 * @uses $this->sitemap_header()
	 * @uses apply_filters
	 * @uses get_terms()
	 * @uses esc_url()
	 * @uses esc_html()
	 * @uses get_term_link()
	 */
	function sitemap_taxonomies() {
		$this->current_request = 'sitemap_taxonomies';

		$this->sitemap_header();

		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		// Get the filtered in custom taxonomies
		$taxonomies = apply_filters( 'scalable_sitemaps_custom_taxonomies', array() );

		// Loop through custom taxonomies
		foreach ( $taxonomies as $taxonomy ) {
			// Get all terms associated with this tax
			$terms = get_terms( $taxonomy );

			// Loop through the terms and create an entry in the sitemap
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					echo "\t<url>\n";
					echo "\t\t<loc>" . esc_url( get_term_link( $term, $taxonomy ) ) . "</loc>\n";
					echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', date( 'Y-m-d H:i:s', time() - ( 60 * 60 * 24 * 2 ) ) ) . '+00:00' ) . "</lastmod>\n";
					echo "\t\t<priority>0.6</priority>\n";
					echo "\t</url>\n";
				}
			}
		}

		echo "</urlset>";

		die();
	}

	/**
	 * Prints out a sitemap with pages
	 *
	 * @uses $this->current_request
	 * @uses $this->sitemap_header()
	 * @uses apply_filters
	 * @uses $this->get_pages()
	 * @uses esc_url()
	 * @uses esc_html()
	 */
	function sitemap_pages() {
		$this->current_request = 'sitemap_pages';
		
		$this->sitemap_header();
		
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		
		// Get all pages and allow themes to filter which pages will be included
		$pages = apply_filters( 'scalable_sitemaps_pages', $this->get_pages() );

		// Loop through pages and print out the reference to each
		if ( is_array( $pages ) ) {
			foreach ( $pages as $page ) {
				echo "\t<url>\n";
				echo "\t\t<loc>" . esc_url( $page[1] ) . "</loc>\n";
				echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', $page[2] ) . '+00:00' ) . "</lastmod>\n";
				echo "\t\t<priority>0.7</priority>\n";
				echo "\t</url>\n";
			}
		}
		
		echo '</urlset>';
		
		die();
	}
	
	/**
	 * Prints a child stylesheet (for a specific day)
	 *
	 * @uses $this->current_request
	 * @uses $this->sitemap_header()
	 * @uses esc_url()
	 * @uses esc_html()
	 * @uses $this->get_images_attached_to_post()
	 */
	function sitemap_child( $datestamp ) {
		$this->current_request = 'sitemap_child';

		// Sanitize the datestamp
		$datestamp = sanitize_text_field( $datestamp );

		// Get all posts published on the requested day
		$urls = $this->get_posts_published_on( $datestamp );
		
		// Print sitemap header
		$this->sitemap_header();
		
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
		
		// Loop through URLs for this day and print reference to each
		foreach ( $urls as $url ) {
			echo "\t<url>\n";
			echo "\t\t<loc>" . esc_url( $url[1] ) . "</loc>\n";
			echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', $url[2] ) . '+00:00' ) . "</lastmod>\n";
			echo "\t\t<changefreq>weekly</changefreq>\n";
			echo "\t\t<priority>0.8</priority>\n";
			
			// Get images attached to this post
			$images = $this->get_images_attached_to_post( $url[0] );
			
			// Loop through attached images and print a reference to each
			foreach ( $images as $image ) {
				if ( ! empty( $image[1] ) && ! empty( $image[2] ) ) {
					echo "\t\t<image:image>\n";
					echo "\t\t\t<image:loc>" . esc_url( $image[1] ) . "</image:loc>\n";
					echo "\t\t\t<image:caption>" . esc_html( strip_tags( $image[2] ) ) . "</image:caption>\n";
					echo "\t\t</image:image>\n";
				}
			}
			
			echo "\t</url>\n";
		}
		
		echo "</urlset>";

		die();
	}
	
	/**
	 * Prints the news sitemap
	 *
	 * @uses $this->current_request
	 * @uses $this->get_news_posts()
	 * @uses $this->sitemap_header()
	 * @uses esc_url()
	 * @uses esc_html()
	 */
	function sitemap_news() {
		$this->current_request = 'sitemap_news';
		
		// Get all valid news posts
		$urls = $this->get_news_posts();
		
		// Print sitemap header
		$this->sitemap_header();
		
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
		
		// Loop through URLs and print a reference to each
		foreach ( $urls as $url ) {
			echo "\t<url>\n";
			echo "\t\t<loc>" . esc_url( $url[1] ) . "</loc>\n";
			echo "\t\t<lastmod>" . esc_html( str_replace( ' ', 'T', $url[2] ) . '+00:00' ) . "</lastmod>\n";
			echo "\t\t<changefreq>weekly</changefreq>\n";
			echo "\t\t<priority>0.8</priority>\n";
			echo "\t</url>\n";
		}
		
		echo '</urlset>';

		die();
	}
	
	/**
	 * Get an array of posts considered news posts
	 *
	 * @global $post
	 *
	 * @uses apply_filters()
	 * @uses absint()
	 * @uses WP_Query
	 * @uses get_permalink()
	 * @uses get_the_time()
	 *
	 * @return array Post IDs, URLs, and publish time
	 */
	function get_news_posts() {
		global $post;
		
		// Get the proper slug for the news category
		$news_category = apply_filters( 'scalable_sitemaps_news_category', 'news' );

		// Bail if something has falsified the news category (allows disabling the news sitemap)
		if ( false === $news_category )
			return false;
		
		// Get a count of days to include in the news sitemap
		$dayscount = absint( apply_filters( 'scalable_sitemaps_news_days', 2 ) );
		
		// Do a query for all posts in the news category in the specified days range
		$query = new WP_Query( array( 
			'fields' => 'ids', 
			'posts_per_page' => 1000, 
			'date_query' => array( 'after' => absint( $dayscount ) . ' Days Ago', ),
			'category_name' => $news_category, 
		));

		$urls = array();

		// Loop through query posts and formulate a results array
		foreach ( $query->posts as $post_id ) {
			$urls[] = array( absint( $post_id ), get_permalink( $post_id ), get_the_time( 'Y-m-d H:i:s', $post_id ) );
		}
		
		return $urls;
	}
	
	/**
	 * Return an array of images attached to a post
	 *
	 * @param int $post_id The post ID to get images from
	 *
	 * @uses wp_cache_get()
	 * @uses wp_cache_set()
	 * @uses get_posts()
	 *
	 * @return array Image IDs, GUIDs, and excerpts
	 */
	function get_images_attached_to_post( $post_id ) {
		// Try to get images from cache
		$urls = wp_cache_get( 'images_' . $post_id, 'ssitemaps' );

		// No cache value, so do a query
		if ( ! $urls ) {
			// Get attached images with get_posts
			$images = get_posts( array( 
				'post_parent' => $post_id, 
				'post_type' => 'attachment', 
				'posts_per_page' => -1, 
			));
			
			$urls = array();
			
			// Create a results array
			foreach ( $images as $image ) {
				$urls[] = array( $image->ID, $image->guid, $image->post_excerpt );
			}

			// Store the results in cache for next hit
			wp_cache_set( 'images_' . $post_id, $urls, 'ssitemaps', 3600 );
		}
			
		return $urls;
	}
	
	/**
	 * Get an array of posts published on a particular datestamp
	 *
	 * @param int $datestamp The datestamp to get posts for
	 *
	 * @uses WP_Query
	 * @uses get_permalink()
	 * @uses get_the_time()
	 *
	 * @return array Post IDs, permalinks, and publish times
	 */
	function get_posts_published_on( $datestamp ) {
		// Run a query for the datestamp
		$query = new WP_Query( array( 
			'post_type' => apply_filters( 'scalable_sitemaps_post_types', array( 'post' ) ),
			'date_query' => array( 
				'year' => date( 'Y', strtotime( $datestamp ) ), 
				'month' => date( 'm', strtotime( $datestamp ) ), 
				'day' => date( 'd', strtotime( $datestamp ) ), 
			), 
			'fields' => 'ids', 
		));
		
		$urls = array();
		
		// Loop through query posts and create a results array
		foreach ( $query->posts as $post_id ) {
			$urls[] = array( $post_id, get_permalink( $post_id ), get_the_time( 'Y-m-d H:i:s', $post_id ) );
		}

		return $urls;
	}
	
	/**
	 * Get an array of all pages
	 *
	 * @uses WP_Query
	 * @uses get_permalink()
	 * @uses get_the_time()
	 * 
	 * @return array Post IDs, Permalinks, and publish times
	 */
	function get_pages() {
		// Do a query for all pages on this site
		$query = new WP_Query( array( 
			'fields' => 'ids', 
			'post_type' => 'page', 
		));

		$urls = array();
		
		// Loop through pages and create a results array
		foreach ( $query->posts as $post_id ) {
			$urls[] = array( $post_id, get_permalink( $post_id ), get_the_time( 'Y-m-d H:i:s', $post_id ) );
		}
		
		return $urls;
	}
	
	/**
	 * Get an array of all dates that posts were published on
	 *
	 * @global $wpdb WordPress Database
	 *
	 * @uses wp_cache_get()
	 * @uses wp_cache_set()
	 * @uses $wpdb->get_col()
	 * @uses $wpdb->prepare()
	 * @uses apply_filters()
	 * 
	 * @return array Dates and timestamp slugs
	 */
	function get_all_dates() {
		global $wpdb;

		// Try to get dates from the cache
		$all_dates = wp_cache_get( 'all_dates', 'ssitemaps' );

		// No cache, do a MySQL query to get the dates
		if ( ! $all_dates ) {
			// Get custom post types if configured
			$post_types = apply_filters( 'scalable_sitemaps_post_types', array( 'post' ) );

			$dates = array();

			// Loop through post types and query for dates of each
			foreach ( $post_types as $post_type ) {
				$type_dates = $wpdb->get_col( $wpdb->prepare( "
					SELECT DISTINCT CAST(post_date as date)
					FROM {$wpdb->posts}
					WHERE post_status='publish'
					AND post_type=%s
					ORDER BY ID DESC
				", $post_type ) );

				// Merge type dates into master dates array
				$dates = array_merge( $dates, $type_dates );
			}

			// Store the dates in cache for the next hit
			wp_cache_set( 'all_dates', $dates, 'ssitemaps', 3600 );
		}
		
		$dates_and_slugs = array();
		
		// Loop through dates and create a results array
		foreach ( $dates as $date ) {
			$dates_and_slugs[] = array( $date, str_replace( '-', '', $date ) );
		}
		
		return $dates_and_slugs;
	}
	
}

// Create the sitemap object
Scalable_Sitemaps::get_instance();

// omit