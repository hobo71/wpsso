<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSubmenuGeneral' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoSubmenuGeneral extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_opengraph',
				_x( 'All Social Websites / Open Graph', 'metabox title', 'wpsso' ), 
					array( &$this, 'show_metabox_opengraph' ), $this->pagehook, 'normal' );

			add_meta_box( $this->pagehook.'_publishers',
				_x( 'Specific Websites and Publishers', 'metabox title', 'wpsso' ), 
					array( &$this, 'show_metabox_publishers' ), $this->pagehook, 'normal' );

			// issues a warning notice if the default image size is too small
			if ( ! SucomUtil::get_const( 'WPSSO_CHECK_DEFAULT_IMAGE' ) )
				$og_image = $this->p->media->get_default_image( 1, $this->p->cf['lca'].'-opengraph', false );
		}

		public function show_metabox_opengraph() {
			$metabox = 'og';
			$tabs = apply_filters( $this->p->cf['lca'].'_general_og_tabs', array( 
				'general' => _x( 'Site Information', 'metabox tab', 'wpsso' ),
				'content' => _x( 'Descriptions', 'metabox tab', 'wpsso' ),	// same text as Social Settings tab
				'author' => _x( 'Authorship', 'metabox tab', 'wpsso' ),
				'images' => _x( 'Images', 'metabox tab', 'wpsso' ),
				'videos' => _x( 'Videos', 'metabox tab', 'wpsso' ),
			) );
			$rows = array();
			foreach ( $tabs as $key => $title )
				$rows[$key] = apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
					$this->get_rows( $metabox, $key ), $this->form );
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		public function show_metabox_publishers() {
			$metabox = 'pub';
			$tabs = apply_filters( $this->p->cf['lca'].'_general_pub_tabs', array( 
				'facebook' => _x( 'Facebook', 'metabox tab', 'wpsso' ),
				'google' => _x( 'Google / Schema', 'metabox tab', 'wpsso' ),
				'pinterest' => _x( 'Pinterest', 'metabox tab', 'wpsso' ),
				'twitter' => _x( 'Twitter', 'metabox tab', 'wpsso' ),
				'other' => _x( 'Other', 'metabox tab', 'wpsso' ),
			) );
			$rows = array();
			foreach ( $tabs as $key => $title )
				$rows[$key] = apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows',
					$this->get_rows( $metabox, $key ), $this->form );
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();
			$user_names = $this->p->m['util']['user']->get_form_display_names();
			$user_contacts = $this->p->m['util']['user']->get_form_contact_fields();

			switch ( $metabox.'-'.$key ) {

				case 'og-general':

					$rows['og_art_section'] = $this->p->util->get_th( _x( 'Default Article Topic',
						'option label', 'wpsso' ), null, 'og_art_section' ).
					'<td>'.$this->form->get_select( 'og_art_section', $this->p->util->get_topics() ).'</td>';

					$rows['og_site_name'] = $this->p->util->get_th( _x( 'Site Name',
						'option label', 'wpsso' ), null, 'og_site_name', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_input( SucomUtil::get_locale_key( 'og_site_name' ), 
						null, null, null, get_bloginfo( 'name', 'display' ) ).'</td>';

					$rows['og_site_description'] = $this->p->util->get_th( _x( 'Site Description',
						'option label', 'wpsso' ), null, 'og_site_description', array( 'is_locale' => true ) ).
					'<td>'.$this->form->get_textarea( SucomUtil::get_locale_key( 'og_site_description' ), 
						null, null, null, get_bloginfo( 'description', 'display' ) ).'</td>';

					break;

				case 'og-content':

					$rows['og_title_sep'] = $this->p->util->get_th( _x( 'Title Separator',
						'option label', 'wpsso' ), null, 'og_title_sep' ).
					'<td>'.$this->form->get_input( 'og_title_sep', 'short' ).'</td>';

					$rows['og_title_len'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Maximum Title Length',
						'option label', 'wpsso' ), null, 'og_title_len' ).
					'<td>'.$this->form->get_input( 'og_title_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'wpsso' ).'</td>';

					$rows['og_desc_len'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Maximum Description Length',
						'option label', 'wpsso' ), null, 'og_desc_len' ).
					'<td>'.$this->form->get_input( 'og_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'wpsso' ).'</td>';

					$rows['og_desc_hashtags'] = $this->p->util->get_th( _x( 'Add Hashtags to Descriptions',
						'option label', 'wpsso' ), null, 'og_desc_hashtags' ).
					'<td>'.$this->form->get_select( 'og_desc_hashtags', 
						range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).
							' '._x( 'tag names', 'option comment', 'wpsso' ).'</td>';

					$rows['og_page_title_tag'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Add Page Title in Tags / Hashtags',
						'option label', 'wpsso' ), null, 'og_page_title_tag' ).
					'<td>'.$this->form->get_checkbox( 'og_page_title_tag' ).'</td>';

					$rows['og_page_parent_tags'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Add Parent Page Tags / Hashtags',
						'option label', 'wpsso' ), null, 'og_page_parent_tags' ).
					'<td>'.$this->form->get_checkbox( 'og_page_parent_tags' ).'</td>';

					break;

				case 'og-author':

					$rows['og_author_field'] = $this->p->util->get_th( _x( 'Author Profile URL Field',
						'option label', 'wpsso' ), null, 'og_author_field' ).
					'<td>'.$this->form->get_select( 'og_author_field', $user_contacts ).'</td>';

					$rows['og_author_fallback'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Fallback to Author\'s Archive Page',
						'option label', 'wpsso' ), null, 'og_author_fallback' ).
					'<td>'.$this->form->get_checkbox( 'og_author_fallback' ).'</td>';

					$rows['og_def_author_id'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Default Author when Missing',
						'option label', 'wpsso' ), null, 'og_def_author_id' ).
					'<td>'.$this->form->get_select( 'og_def_author_id', $user_names, null, null, true ).'</td>';

					$rows['og_def_author_on_index'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Default Author on Indexes',
						'option label', 'wpsso' ), null, 'og_def_author_on_index' ).
					'<td>'.$this->form->get_checkbox( 'og_def_author_on_index' ).' '.
						_x( 'defines index / archive webpages as articles', 'option comment', 'wpsso' ).'</td>';

					$rows['og_def_author_on_search'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Default Author on Search Results',
						'option label', 'wpsso' ), null, 'og_def_author_on_search' ).
					'<td>'.$this->form->get_checkbox( 'og_def_author_on_search' ).' '.
						_x( 'defines search webpages as articles', 'option comment', 'wpsso' ).'</td>';

					break;

				case 'og-images':

					$rows['og_vid_prev_img'] = $this->p->util->get_th( _x( 'Maximum Images to Include',
						'option label', 'wpsso' ), null, 'og_img_max' ).
					'<td>'.$this->form->get_select( 'og_img_max', 
						range( 0, $this->p->cf['form']['max_media_items'] ), 'short', null, true ).
					( empty( $this->form->options['og_vid_prev_img'] ) ?
						'' : ' '._x( '<em>video preview images are enabled</em> and will be included first',
							'option comment', 'wpsso' ) ).'</td>';

					$rows['og_img'] = $this->p->util->get_th( _x( 'Open Graph Image Dimensions',
						'option label', 'wpsso' ), null, 'og_img_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'og_img', false, false ).'</td>';

					$rows['og_def_img_id'] = $this->p->util->get_th( _x( 'Default / Fallback Image ID',
						'option label', 'wpsso' ), null, 'og_def_img_id' ).
					'<td>'.$this->form->get_image_upload_input( 'og_def_img' ).'</td>';

					$rows['og_def_img_url'] = $this->p->util->get_th( _x( 'or Default / Fallback Image URL',
						'option label', 'wpsso' ), null, 'og_def_img_url' ).
					'<td>'.$this->form->get_image_url_input( 'og_def_img' ).'</td>';

					$rows['og_def_img_on_index'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Default Image on Indexes',
						'option label', 'wpsso' ), null, 'og_def_img_on_index' ).
					'<td>'.$this->form->get_checkbox( 'og_def_img_on_index' ).'</td>';

					$rows['og_def_img_on_search'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Default Image on Search Results',
						'option label', 'wpsso' ), null, 'og_def_img_on_search' ).
					'<td>'.$this->form->get_checkbox( 'og_def_img_on_search' ).'</td>';

					if ( $this->p->is_avail['media']['ngg'] === true ) {
						$rows['og_ngg_tags'] = '<tr class="hide_in_basic">'.
						$this->p->util->get_th( _x( 'Add Tags from NGG Featured Image',
							'option label', 'wpsso' ), null, 'og_ngg_tags' ).
						'<td>'.$this->form->get_checkbox( 'og_ngg_tags' ).'</td>';
					}

					break;

				case 'og-videos':

					break;

				case 'pub-facebook':

					$rows['fb_publisher_url'] = $this->p->util->get_th( _x( 'Facebook Business Page URL',
						'option label', 'wpsso' ), null, 'fb_publisher_url' ).
					'<td>'.$this->form->get_input( 'fb_publisher_url', 'wide' ).'</td>';

					$rows['fb_app_id'] = $this->p->util->get_th( _x( 'Facebook Application ID',
						'option label', 'wpsso' ), null, 'fb_app_id' ).
					'<td>'.$this->form->get_input( 'fb_app_id' ).'</td>';

					$rows['fb_admins'] = $this->p->util->get_th( _x( 'or Facebook Admin Username(s)',
						'option label', 'wpsso' ), null, 'fb_admins' ).
					'<td>'.$this->form->get_input( 'fb_admins' ).'</td>';

					$rows['seo_author_name'] = $this->p->util->get_th( _x( 'Author Name Format',
						'option label', 'wpsso' ), null, 'google_author_name' ).
					'<td>'.$this->form->get_select( 'seo_author_name', 
						$this->p->cf['form']['user_name_fields'] ).'</td>';

					$rows['fb_lang'] = $this->p->util->get_th( _x( 'Default Content Language',
						'option label', 'wpsso' ), null, 'fb_lang' ).
					'<td>'.$this->form->get_select( 'fb_lang', SucomUtil::get_pub_lang( 'facebook' ) ).'</td>';

					break;

				case 'pub-google':

					$rows['seo_publisher_url'] = $this->p->util->get_th( _x( 'Google+ Business Page URL',
						'option label', 'wpsso' ), null, 'google_publisher_url' ).
					'<td>'.$this->form->get_input( 'seo_publisher_url', 'wide' ).'</td>';

					$rows['seo_desc_len'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Search / SEO Description Length',
						'option label', 'wpsso' ), null, 'google_desc_len' ).
					'<td>'.$this->form->get_input( 'seo_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'wpsso' ).'</td>';

					$rows['seo_author_field'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Author Link URL Field',
						'option label', 'wpsso' ), null, 'google_author_field' ).
					'<td>'.$this->form->get_select( 'seo_author_field', $user_contacts ).'</td>';

					$rows['seo_def_author_id'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Default Author when Missing',
						'option label', 'wpsso' ), null, 'google_def_author_id' ).
					'<td>'.$this->form->get_select( 'seo_def_author_id', $user_names, null, null, true ).'</td>';

					$rows['seo_def_author_on_index'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Default Author on Indexes',
						'option label', 'wpsso' ), null, 'google_def_author_on_index' ).
					'<td>'.$this->form->get_checkbox( 'seo_def_author_on_index' ).'</td>';

					$rows['seo_def_author_on_search'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Default Author on Search Results',
						'option label', 'wpsso' ), null, 'google_def_author_on_search' ).
					'<td>'.$this->form->get_checkbox( 'seo_def_author_on_search' ).'</td>';

					$rows['subsection_google_schema'] = '<td></td><td class="subsection"><h4>'.
						_x( 'Google Structured Data / Schema Markup',
							'metabox title', 'wpsso' ).'</h4></td>';

					$rows['schema_add_noscript'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Use Meta Property Containers',
						'option label', 'wpsso' ), null, 'google_schema_add_noscript' ).
					'<td>'.$this->form->get_checkbox( 'schema_add_noscript' ).'</td>';

					$users = SucomUtil::get_user_select( array( 'editor', 'administrator' ) );
					$rows['schema_json'] = $this->p->util->get_th( _x( 'Include Google Structured Data',
						'option label', 'wpsso' ), null, 'google_schema_json' ).
					'<td>'.
					'<p>'.$this->form->get_checkbox( 'schema_website_json' ).' '.
						sprintf( __( '<a href="%s">WebSite Information</a> for Google Search',
							'wpsso' ), 'https://developers.google.com/structured-data/site-name' ).'</p>'.
					'<p>'.$this->form->get_checkbox( 'schema_organization_json' ).
						' Site Publisher / <a href="https://developers.google.com/structured-data/customize/social-profiles">'.
							'Organization Social Profile</a></p>'.
					'<p>'.$this->form->get_checkbox( 'schema_person_json' ).
						' <a href="https://developers.google.com/structured-data/customize/social-profiles">'.
							'Person Social Profile</a> for the Site Owner '.
								$this->form->get_select( 'schema_person_id', $users, null, null, true ).'</p>'.
					'</td>';

					$rows['schema_alt_name'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Website Alternate Name',
						'option label', 'wpsso' ), null, 'google_schema_alt_name' ).
					'<td>'.$this->form->get_input( 'schema_alt_name', 'wide' ).'</td>';

					$rows['schema_logo_url'] = $this->p->util->get_th( '<a href="https://developers.google.com/structured-data/customize/logos">'.
						_x( 'Business Logo Image URL', 'option label', 'wpsso' ).'</a>', null, 'google_schema_logo_url' ).
					'<td>'.$this->form->get_input( 'schema_logo_url', 'wide' ).'</td>';

					$rows['schema_banner_url'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Business Banner (600x60) Image URL',
						'option label', 'wpsso' ), null, 'google_schema_banner_url' ).
					'<td>'.$this->form->get_input( 'schema_banner_url', 'wide' ).'</td>';

					$rows['schema_img'] = $this->p->util->get_th( _x( 'Schema Image Dimensions',
						'option label', 'wpsso' ), null, 'google_schema_img_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'schema_img', false, false ).'</td>';

					$rows['schema_desc_len'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Maximum Description Length',
						'option label', 'wpsso' ), null, 'google_schema_desc_len' ).
					'<td>'.$this->form->get_input( 'schema_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'wpsso' ).'</td>';

					$rows['schema_author_name'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Author / Person Name Format',
						'option label', 'wpsso' ), null, 'google_schema_author_name' ).
					'<td>'.$this->form->get_select( 'schema_author_name', 
						$this->p->cf['form']['user_name_fields'] ).'</td>';

					$schema_types = $this->p->schema->get_schema_types_select();
					$schema_select = '';
					foreach ( $this->p->util->get_post_types() as $post_type )
						$schema_select .= '<p>'.$this->form->get_select( 'schema_type_for_'.$post_type->name,
							$schema_types, 'schema_type' ).' for '.$post_type->label.'</p>'."\n";

					$rows['schema_type_for_home_page'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Schema Item Type for Home Page',
						'option label', 'wpsso' ), null, 'google_schema_home_page' ).
					'<td>'.$this->form->get_select( 'schema_type_for_home_page', $schema_types, 'schema_type' ).'</td>';

					$rows['schema_type_for_ptn'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Schema Item Type by Post Type',
						'option label', 'wpsso' ), null, 'google_schema_type_for_ptn' ).
					'<td>'.$schema_select.'</td>';

					break;

				case 'pub-pinterest':

					$rows[] = '<td colspan="2" style="padding-bottom:10px;">'.
						$this->p->msgs->get( 'info-pub-pinterest' ).'</td>';

					$rows['rp_publisher_url'] = $this->p->util->get_th( _x( 'Pinterest Company Page URL',
						'option label', 'wpsso' ), null, 'rp_publisher_url'  ).
					'<td>'.$this->form->get_input( 'rp_publisher_url', 'wide' ).'</td>';

					if ( ! SucomUtil::get_const( 'WPSSO_RICH_PIN_DISABLE' ) ) {
						$rows['rp_img'] = $this->p->util->get_th( _x( 'Rich Pin Image Dimensions',
							'option label', 'wpsso' ), null, 'rp_img_dimensions' ).
						'<td>'.$this->form->get_image_dimensions_input( 'rp_img' ).'</td>';
					}

					$rows['rp_author_name'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Author Name Format',
						'option label', 'wpsso' ), null, 'rp_author_name' ).
					'<td>'.$this->form->get_select( 'rp_author_name',
						$this->p->cf['form']['user_name_fields'] ).'</td>';

					$rows['rp_dom_verify'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Pinterest Website Verification ID',
						'option label', 'wpsso' ), null, 'rp_dom_verify' ).
					'<td>'.$this->form->get_input( 'rp_dom_verify', 'api_key' ).'</td>';

					break;

				case 'pub-twitter':

					$rows[] = '<td colspan="2" style="padding-bottom:10px;">'.
						$this->p->msgs->get( 'info-pub-twitter' ).'</td>';

					$rows['tc_site'] = $this->p->util->get_th( _x( 'Twitter Business @username',
						'option label', 'wpsso' ), null, 'tc_site' ).
					'<td>'.$this->form->get_input( 'tc_site' ).'</td>';

					$rows['tc_desc_len'] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Maximum Description Length',
						'option label', 'wpsso' ), null, 'tc_desc_len' ).
					'<td>'.$this->form->get_input( 'tc_desc_len', 'short' ).' '.
						_x( 'characters or less', 'option comment', 'wpsso' ).'</td>';

					$rows['tc_sum'] = $this->p->util->get_th( _x( '<em>Summary</em> Card Image Dimensions',
						'option label', 'wpsso' ), null, 'tc_sum_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'tc_sum', false, false ).'</td>';

					$rows['tc_lrgimg'] = $this->p->util->get_th( _x( '<em>Large Image</em> Card Image Dimensions',
						'option label', 'wpsso' ), null, 'tc_lrgimg_dimensions' ).
					'<td>'.$this->form->get_image_dimensions_input( 'tc_lrgimg', false, false ).'</td>';

					break;

				case 'pub-other':

					$rows['instgram_publisher_url'] = $this->p->util->get_th( _x( 'Instagram Business URL',
						'option label', 'wpsso' ), null, 'instgram_publisher_url' ).
					'<td>'.$this->form->get_input( 'instgram_publisher_url', 'wide' ).'</td>';

					$rows['linkedin_publisher_url'] = $this->p->util->get_th( _x( 'LinkedIn Company Page URL',
						'option label', 'wpsso' ), null, 'linkedin_publisher_url'  ).
					'<td>'.$this->form->get_input( 'linkedin_publisher_url', 'wide' ).'</td>';

					$rows['myspace_publisher_url'] = $this->p->util->get_th( _x( 'MySpace Business (Brand) URL',
						'option label', 'wpsso' ), null, 'myspace_publisher_url'  ).
					'<td>'.$this->form->get_input( 'myspace_publisher_url', 'wide' ).'</td>';

					break;
			}
			return $rows;
		}
	}
}

?>
