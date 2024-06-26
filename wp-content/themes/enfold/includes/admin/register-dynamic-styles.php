<?php
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! function_exists( 'avia_prepare_dynamic_styles' ) )
{
	/**
	 *
	 *
	 * @param array|false $options			options from page "avia" in top level array
	 */
	function avia_prepare_dynamic_styles( $options = false )
	{
		global $avia_config;

		/**
		 * We rely that all options are in page "avia"
		 * If this ever changes we have to recheck logic here
		 */
		if( ! $options )
		{
			$options = avia_get_option();
		}

		$color_set = array();
		$styles = array();
		$typos = array();

		$post_id = avia_get_the_ID();

		/**
		 * @params array $options
		 * @return array
		 */
		$options = apply_filters( 'avia_pre_prepare_colors', $options );

		if( ! is_array( $options ) )
		{
			$options = array();
		}

		/**
		 * fix an inconsistent usage of option color-default_font_size in case update routine did not work
		 * Can be removed in a future release
		 *
		 * @since 4.8.8
		 */
		if( isset( $options['color-default_font_size'] ) )
		{
			$options['typo-default_font_size'] = $options['color-default_font_size'];
			unset( $options['color-default_font_size'] );
		}

		//boxed or stretched layout
		$avia_config['box_class'] = empty( $options['color-body_style'] ) ? 'stretched' : $options['color-body_style'];

		//transparency color for header menu
		$avia_config['backend_colors']['menu_transparent'] = empty( $options['header_replacement_menu'] ) ? '' : $options['header_replacement_menu'];
		$avia_config['backend_colors']['menu_transparent_hover'] = empty( $options['header_replacement_menu_hover'] ) ? '' : $options['header_replacement_menu_hover'];

		//custom color for burger menu
		$avia_config['backend_colors']['burger_color'] = empty( $options['burger_color'] ) ? '' : $options['burger_color'];

		//custom width for burger menu flyout
		$avia_config['backend_colors']['burger_flyout_width'] = empty( $options['burger_flyout_width'] ) ? '' : $options['burger_flyout_width'];

		//iterate over the options array to get the color and bg image options and save them to new array
		foreach( $options as $key => $option )
		{
			if( strpos( $key, 'colorset-' ) === 0 )
			{
				$newkey = explode( '-', $key );

				//add the option to the new array
				$color_set[ $newkey[1] ][ $newkey[2] ] = $option;
			}
			else if( strpos( $key, 'color-' ) === 0 )
			{
				$newkey = explode( '-', $key );

				//add the option to the new array
				$styles[ $newkey[1] ] = $option;
			}
			else if( strpos( $key, 'typo-' ) === 0 )
			{
				$newkey = explode( '-', $key, 2 );

				//add the option to the new array
				$typos[ $newkey[1] ] = $option;
			}
		}

		//make sure that main color is added later than alternate color so we can nest main color elements within alternate color elements and the styling is applied.
		$color_set = array_reverse( $color_set );

		######################################################################
		# optimize the styles array and set teh background image and sizing
		######################################################################

		/* only needed if we got a boxed layout option */
		if( empty( $styles['body_img'] ) )
		{
			$styles['body_img'] = '';
		}

		if( empty( $styles['body_repeat'] ) )
		{
			$styles['body_repeat'] = 'no-repeat';
		}

		if( empty( $styles['body_attach'] ) )
		{
			$styles['body_attach'] = 'fixed';
		}

		if( empty( $styles['body_pos'] ) )
		{
			$styles['body_pos'] = 'top left';
		}

		if( $styles['body_img'] == 'custom' )
		{
			$styles['body_img'] = $styles['body_customimage'];
			unset( $styles['body_customimage'] );
		}

		if( $styles['body_repeat']  == 'fullscreen' )
		{
			$styles['body_img'] = trim( $styles['body_img'] );

			if( ! empty( $styles['body_img'] ) )
			{
				$avia_config['fullscreen_image'] = str_replace( '{{AVIA_BASE_URL}}', AVIA_BASE_URL, $styles['body_img'] );
			}
			unset( $styles['body_img'] );
			$styles['body_background'] = '';
		}
		else
		{
			$styles['body_img'] = trim( $styles['body_img'] );
			$url = empty( $styles['body_img'] ) ? '' : "url({$styles['body_img']})";

			$bg = empty( $styles['body_color'] ) ? 'transparent' : $styles['body_color'];
			$styles['body_background'] = "{$bg} {$url} {$styles['body_pos']} {$styles['body_repeat']} {$styles['body_attach']}";
		}

		/**
		 * optimize the array to make it smaller
		 * =====================================
		 */
		foreach( $color_set as $key => $set )
		{
			if( $color_set[ $key ]['bg'] == '' )
			{
				$color_set[ $key ]['bg'] = 'transparent';
			}

			if( $color_set[ $key ]['bg2'] == '' )
			{
				$color_set[ $key ]['bg2'] = 'transparent';
			}

			if( $color_set[ $key ]['primary'] == '' )
			{
				$color_set[ $key ]['primary'] = 'transparent';
			}

			if( $color_set[ $key ]['secondary'] == '' )
			{
				$color_set[$key]['secondary'] = 'transparent';
			}

			if( $color_set[ $key ]['color'] == '' )
			{
				$color_set[ $key ]['color'] = 'transparent';
			}

			if( $color_set[ $key ]['border'] == '' )
			{
				$color_set[ $key ]['border'] = 'transparent';
			}

			if( $color_set[ $key ]['img'] == 'custom' )
			{
				$color_set[ $key ]['img'] = $color_set[ $key ]['customimage'];
				unset( $color_set[ $key ]['customimage'] );
			}

			if( $color_set[ $key ]['img'] == '' )
			{
				unset( $color_set[ $key ]['img'], $color_set[ $key ]['pos'], $color_set[ $key ]['repeat'], $color_set[ $key ]['attach'] );
			}
			else
			{
				$bg = empty( $color_set[ $key ]['bg'] ) ? 'transparent' : $color_set[ $key ]['bg'];
				$repeat = $color_set[ $key ]['repeat'] == 'fullscreen' ? 'no-repeat' : $color_set[ $key ]['repeat'];

				$color_set[ $key ]['img'] = trim( $color_set[ $key ]['img'] );

				if( is_numeric( $color_set[ $key ]['img'] ) )
				{
					$color_set[ $key ]['img'] = wp_get_attachment_image_src( $color_set[ $key ]['img'], 'full' );
					$color_set[ $key ]['img'] = is_array( $color_set[ $key ]['img'] ) ? $color_set[ $key ]['img'][0] : '';
				}

				$url = empty( $color_set[ $key ]['img'] ) ? '' : "url({$color_set[ $key ]['img']})";

				$color_set[ $key ]['background_image'] = "{$bg} {$url} {$color_set[ $key ]['pos']} {$repeat} {$color_set[ $key ]['attach']}";
			}

			if( isset( $color_set[ $key ]['customimage'] ) )
			{
				unset( $color_set[ $key ]['customimage'] );
			}

			if( empty( $color_set[ $key ]['heading'] ) )
			{
				//checks if we have a dark or light background and then creates a stronger version of the main font color for headings
				$shade = avia_backend_calc_preceived_brightness( $color_set[ $key ]['bg'], 100 ) ? 'lighter' : 'darker';
				$color_set[ $key ]['heading'] = avia_backend_calculate_similar_color( $color_set[ $key ]['color'], $shade, 4 );
			}

			if( empty( $color_set[ $key ]['meta'] ) )
			{
				// creates a new color from the background color and the heading color (results in a lighter color)
				$color_set[ $key ]['meta'] = avia_backend_merge_colors( $color_set[ $key ]['heading'], $color_set[ $key ]['bg'] );
			}
		}

		/**
		 * Add extra calculated colors
		 *
		 * @since 5.7
		 */
		foreach( $color_set as $section_key => $colors )
		{
			$colors['constant_font'] = avia_backend_calc_preceived_brightness( $colors['primary'], 230 ) ? '#ffffff' : $colors['bg'];
			$colors['button_border'] = avia_backend_calculate_similar_color( $colors['primary'], 'darker', 2 );
			$colors['button_border2'] = avia_backend_calculate_similar_color( $colors['secondary'], 'darker', 2 );
			$colors['iconlist'] = avia_backend_calculate_similar_color( $colors['border'], 'darker', 1 );
			$colors['timeline'] = avia_backend_calculate_similar_color( $colors['border'], 'darker', 1 );
			$colors['timeline_date'] = avia_backend_calculate_similar_color( $colors['border'], 'darker', 4 );
			$colors['masonry'] = avia_backend_calculate_similar_color( $colors['bg2'], 'darker', 1 );
			$colors['stripe']  = avia_backend_calculate_similar_color( $colors['primary'], 'lighter', 2 );
			$colors['stripe2'] = avia_backend_calculate_similar_color( $colors['primary'], 'lighter', 1 );
			$colors['stripe2nd'] = avia_backend_calculate_similar_color( $colors['secondary'], 'lighter', 1 );
			$colors['button_font'] = avia_backend_calc_preceived_brightness( $colors['primary'], 230 ) ? '#ffffff' : $colors['bg'];

			/**
			 * Add calculated colors. Make sure index is unique !!!!
			 *
			 * @used_by			enfold\config-events-calendar\event-mod-css-dynamic.php			10
			 * @used_by			enfold\config-woocommerce\woocommerce-mod-css-dynamic.php		10
			 * @not_used_by		Avia_WC_Block_Editor
			 *
			 * @since 5.7
			 * @param array $colors
			 * @param string $section_key
			 * @return array
			 */
			$color_set[ $section_key ] = apply_filters( 'avf_dynamic_css_calculated_colors', $colors, $section_key );
		}

		

		$avia_config['backend_colors']['color_set'] = $color_set;
		$avia_config['backend_colors']['style'] = $styles;
		$avia_config['backend_colors']['typos'] = $typos;

		require( AVIA_BASE . 'css/dynamic-css.php' );

	}

}
