<?php
/*
Plugin Name: Advanced Ads - Repeteable ads
Description: Allows the insertion of ads between a given number of paragraphs
Version: 0.1
Author: Álvaro Martinez Majado <a@alvaro.xyz>
License: GPL2+
*/

/*
 * Prints a ckeckbox in Advanced Ads Placement options to make an ad
 * repeatable.
 */

function advanced_ads_repeteable_add_repeat_option_checkbox ( $_placement_slug )
{

	// $_placement_slug is passed to the hook by Advanced Ads

	// grab options for placements
	$saved_advanced_ads_repeteable_options = get_option( "advads-ads-placements" );

	// echo a checkbox in placements options to make an option repeatable
	echo "<p><label><input type=\"checkbox\" name=\"advads[placements][{$_placement_slug}][options][repeat_option]\" value=\"1\"";

	// make the ckeckbox already checked if this is its state in the DB
		if (isset(  $saved_advanced_ads_repeteable_options[$_placement_slug][options][repeat_option] ) ) {
			checked( $saved_advanced_ads_repeteable_options[$_placement_slug][options][repeat_option] , 1);
		}
	echo "/>";
	_e( 'repeat this option', 'advanced-ads-repeteable' );
	"</label></p>";

}

add_action('advanced-ads-placement-options-after', 'advanced_ads_repeteable_add_repeat_option_checkbox');

/*
 * Handles an insertion inside the_content. Takes 2 arguments:
 * $insertion: what to insert inside the_content
 * $paragraph_number: every how many paragraphs
 */

function advanced_ads_insert_between_paragraphs( $insertions, $content ) {

	if ( !empty( $insertions ) ) {

		foreach ($insertions as $key => $insertion) {
					$closing_tag = '</p>';
					$paragraphs = explode( $closing_tag, $content );
					foreach ($paragraphs as $index => $paragraph) {

						if ( trim( $paragraph ) ) {
							$paragraphs[$index] .= $closing_tag;
						}

						if ( is_int( ($index+1) / $insertion['repeat_each'] ) ) {
							$get_ad_content = get_post($insertion['ad_id']);
							$paragraphs[$index] .= $get_ad_content->post_content;

							error_log(print_r($insertion['ad_id'] , true));
						}
					}

					$content = implode( '', $paragraphs );
		}

	}

	return $content;
}

/*
 * Determines what to insert and passes
 * it to advanced_ads_insert_between_paragraphs
 */

function advanced_ads_insert_post_ads( $content ) {
		// gets options from advads-ads-placements (Advanced Ads Placements)
		$saved_advanced_ads_repeteable_options = get_option( "advads-ads-placements" );

		// goes through all saved placements
		foreach ($saved_advanced_ads_repeteable_options as $key => $placement) {

			// if one of this placements has been set as repeteable…
			if (!empty($saved_advanced_ads_repeteable_options[$key]['options']['repeat_option'])) {

				// then add an item to the ads array with the id
				// of the post containing the ad
				// since Simple Ads
				// return $saved_advanced_ads_repeteable_options[$key]['item']
				// as an string starting with "ad_" and followed by the actual
				// id of the ad, and we would like to use only the ID, we use
				// substr to get rid of the first 3 characters

				$ads[$key]['ad_id'] = substr($saved_advanced_ads_repeteable_options[$key]['item'], 3);
				$ads[$key]['repeat_each'] = (int)$saved_advanced_ads_repeteable_options[$key]['options']['index'];
			}
		}

		$content_between_paragraphs = advanced_ads_insert_between_paragraphs( $ads, $content );


		$content = $content_between_paragraphs;

		return $content;

}

add_action('the_content', 'advanced_ads_insert_post_ads');

/*
 * Prevents Advanced Ads from displaying a reapeteable ad
 * to avoid duplicates
 */

function advanced_ads_avoid_duplicate_display_in_repeteable_ads ($output) {

	$placements = get_option( "advads-ads-placements" );
	$ads_to_hide = array();
	foreach ($placements as $key => $placement) {
		if (!empty($placement['options']['repeat_option'])) {
			$ads_to_hide[] = substr($placement['item'], 3);
		}
	}


	foreach ($ads_to_hide as $key => $ad_to_hide) {
		$id_from_outout = array();
	  $they_match = preg_match( '/data-id="([^"]*)"/i',$output, $found_id ) ;

	}

	if (in_array($found_id[1], $ads_to_hide) ) {
		$output = "";
	}

return $output;


}

add_filter('advanced-ads-ad-output', 'advanced_ads_avoid_duplicate_display_in_repeteable_ads');
