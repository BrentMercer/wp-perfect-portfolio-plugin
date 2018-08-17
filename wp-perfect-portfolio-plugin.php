<?php
/*
 * Plugin Name:	WP Perfect Portfolio Plugin
 * Description:	Adds portfolio custom post type to WordPress
 * Plugin URI:	http://brentmercer.com/wp-perfect-portfolio-plugin/
 * Author:		Brent Mercer
 * Author URI:	http://brentmercer.com
 * Version:		1.0.0
 * License:		GPLv2 or later
 * License URI:	https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:	wp-perfect-portfolio-plugin
 * Domain Path:	/languages

Perfect Portfolio is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Perfect Portfolio is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Perfect Portfolio. If not, see https://www.gnu.org/licenses/gpl-2.0.txt.
*/

// Exit is accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

// required plugin class
require_once dirname( __FILE__ ) . '/lib/class-tgm-plugin-activation.php';

class WP_Perfect_Portfolio {
	private static $_instance = null;

	const FIELD_PREFIX = 'bjm_';

	// this needs to be hard-coded, but this serves as a reminder,
	// and a find replace when searching and replacing
	const TEXT_DOMAIN = 'bjm-portfolio-data';

	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	private function __construct() {
		// initialize Portfolio custom post type
		add_action( 'init', 'WP_Perfect_Portfolio::wpppp_register_post_type' );

		// initialize custom fields from Metabox.io:
		// first check for required plugin
		add_action( 'tgmpa_register', array( $this, 'check_required_plugins' ) );
		// then define the fields
		add_filter( 'rwmb_meta_boxes', array( $this, 'metabox_custom_fields' ) );

		// initialize taxonomy
		add_action( 'init', array( $this, 'wpppp_create_taxonomy' ));
	}

	/**
	* Register Portfolio post type.
	*
	*
	**/
	static function wpppp_register_post_type(){
		$labels = array(
			'name'               => _x( 'Portfolio', 'post type general name', 'wp-perfect-portfolio-plugin' ),
			'singular_name'      => _x( 'Portfolio Item', 'post type singular name', 'wp-perfect-portfolio-plugin' ),
			'menu_name'          => _x( 'Portfolio Items', 'admin menu', 'wp-perfect-portfolio-plugin' ),
			'name_admin_bar'     => _x( 'Portfolio Items', 'add new on admin bar', 'wp-perfect-portfolio-plugin' ),
		);
		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'wp-perfect-portfolio-plugin' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'portfolio' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
			'menu_icon'			 => 'dashicons-screenoptions'
		);
		register_post_type( 'wpppp_portfolio', $args );
	}

	/**
	* Register Project Type taxonomy.
	*
	*
	**/
	function wpppp_create_taxonomy(){
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
			'name'              => _x( 'Project Types', 'taxonomy general name', 'wp-perfect-portfolio-plugin' ),
			'singular_name'     => _x( 'Project Types', 'taxonomy singular name', 'wp-perfect-portfolio-plugin' ),
		);
		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'item type' ),
		);
		register_taxonomy( 'wpppp_item_type', 'wpppp_portfolio', $args );
	}

	/**
	 * Activate post type and flush rewrite rules.
	 */
	static function activate(){
		self::wpppp_register_post_type();
		flush_rewrite_rules();
	}

	/**
	 * Implementation of the TGM Plugin Activation library
	 *
	 * Checks for the plugin(s) we need, and displays the appropriate messages
	 */
	function check_required_plugins() {
		$plugins = array(
			array(
				'name'               => 'Meta Box',
				'slug'               => 'meta-box',
				'required'           => true,
				'force_activation'   => false,
				'force_deactivation' => false,
			),
		);

		$config  = array(
			'domain'           => 'jc_movie_reviews',
			'default_path'     => '',
			'parent_slug'      => 'plugins.php',
			'capability'       => 'update_plugins',
			'menu'             => 'install-required-plugins',
			'has_notices'      => true,
			'is_automatic'     => false,
			'message'          => '',
			'strings'          => array(
				'page_title'                      => __( 'Install Required Plugins', 'jc-movie-reviews' ),
				'menu_title'                      => __( 'Install Plugins', 'jc-movie-reviews' ),
				'installing'                      => __( 'Installing Plugin: %s', 'jc-movie-reviews' ),
				'oops'                            => __( 'Something went wrong with the plugin API.', 'jc-movie-reviews' ),
				'notice_can_install_required'     => _n_noop( 'The Movie Reviews plugin depends on the following plugin: %1$s.', 'The Movie Reviews plugin depends on the following plugins: %1$s.' ),
				'notice_can_install_recommended'  => _n_noop( 'The Movie Reviews plugin recommends the following plugin: %1$s.', 'The Movie Reviews plugin recommends the following plugins: %1$s.' ),
				'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ),
				'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ),
				'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ),
				'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ),
				'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ),
				'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ),
				'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
				'activate_link'                   => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
				'return'                          => __( 'Return to Required Plugins Installer', 'jc-movie-reviews' ),
				'plugin_activated'                => __( 'Plugin activated successfully.', 'jc-movie-reviews' ),
				'complete'                        => __( 'All plugins installed and activated successfully. %s', 'jc-movie-reviews' ),
				'nag_type'                        => 'updated',
			)
		);
		tgmpa( $plugins, $config );
	}

	/**
	 * Create custom fields using metabox.io
	 */
	function metabox_custom_fields() {
		// define the movie custom fields
		$meta_boxes[] = array(
			'id'       => 'movie_data',
			'title'    => 'Additional Information',
			'pages'    => array( 'movie_review' ),
			'context'  => 'normal',
			'priority' => 'high',
			'fields' => array(
				array(
					'name'  => 'Release Year',
					'desc'  => 'Year the movie was released',
					'id'    => self::FIELD_PREFIX . 'movie_year',
					'type'  => 'number',
					'std'   => date('Y'),
					'min'   => '1896',
				),
				array(
					'name'  => 'Director',
					'desc'  => 'Who directed this movie',
					'id'    => self::FIELD_PREFIX . 'movie_director',
					'type'  => 'text',
					'std'   => '',
				),
				array(
					'name'  => 'IMDB Link',
					'desc'  => 'Link for this movie on IMDB',
					'id'    => self::FIELD_PREFIX . 'movie_imdb',
					'type'  => 'url',
					'std'   => '',
				),
			)
		);

		// define the review custom field(s)
		$meta_boxes[] = array(
			'id'       => 'review_data',
			'title'    => 'Review',
			'pages'    => array( 'movie_review' ),
			'context'  => 'side',
			'priority' => 'high',
			'fields' => array(
				array(
					'name'    => 'Rating',
					'desc'    => 'On a scale of 1 - 10, 10 being best',
					'id'      => self::FIELD_PREFIX . 'review_rating',
					'type'    => 'select',
					'options' => array(
						'' => __('TBR (To be rated)'),
						1  => __('1 - I walked out. And I was home!'),
						2  => __('2 - I will never get those hours back'),
						3  => __('3 - Not recommended'),
						4  => __('4 - Might stay awake on an airplane for it'),
						5  => __('5 - As they say on the internet: meh'),
						6  => __('6 - Totally decent'),
						7  => __('7 - Quite good. Recommended.'),
						8  => __('8 - One of my favorites of the last X years'),
						9  => __('9 - Loved it, and you probably will too.'),
						10 => __("10 - Life changing! Mine, yours, everyone's!"),
					),
					'std' => '',
				),
			)
		);

		return $meta_boxes;
	}
}

// initialize plugin
add_action( 'plugins_loaded', 'WP_Perfect_Portfolio::instance' );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'WP_Perfect_Portfolio::activate' );
