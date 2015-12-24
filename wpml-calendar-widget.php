<?php
/*
Plugin Name: WPML Calendar Widget
Plugin URI: https://pressurgeons.com/plugins/
Description: Provides a calendar widget that is compatible with WPML
Version: 1.0.0
Author: Ihor Vorotnov
Author URI: https://ihorvorotnov.com
License: GPL v2 or later
*/

function wpml_calendar_widget_init() {

	/**
	 * Run only if WPML plugin is installed and activated
	 */
	if( class_exists( 'WPML_Auto_Loader' ) ) {

		/**
		 * WPML Calendar Widget Class
		 */
		class WPML_Calendar_Widget extends WP_Widget {

			private static $instance = 0;

			public function __construct() {

				parent::__construct(
					'wpml_calendar_widget',
					__( 'WPML Calendar' ),
					array( 'description' => __( 'A WPML-compatible calendar of your site’s Posts.' ) )
				);

			}

			/**
			 * @see WP_Widget::widget
			 */
			public function widget( $args, $instance ) {

				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

				echo $args['before_widget'];
				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}
				if ( 0 === self::$instance ) {
					echo '<div id="calendar_wrap" class="calendar_wrap">';
				} else {
					echo '<div class="calendar_wrap">';
				}
				wpml_get_calendar();
				echo '</div>';
				echo $args['after_widget'];

				self::$instance++;

			} // End widget

			/**
			 * @see WP_Widget::update
			 */
			public function update( $new_instance, $old_instance ) {

				$instance = $old_instance;
				$instance['title'] = sanitize_text_field( $new_instance['title'] );

				return $instance;

			} // End update

			/**
			 * @see WP_Widget::form
			 */
			public function form( $instance ) {

				$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
				$title = sanitize_text_field( $instance['title'] );
				?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

				<?php
			} // End form


		} // End WPML_Calendar_Widget class

		/**
		 * Register WPML Calendar Widget
		 */
		add_action( 'widgets_init', function() {
			register_widget( 'WPML_Calendar_Widget' );
		});

	} // End if class_exists wrapper

	/**
	 * Build custom calendar
	 */
	function wpml_get_calendar( $initial = true , $echo = true ) {

		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

		// WPML table name
		$wpml_icl_table = $wpdb->prefix . 'icl_translations';

		$key = md5( $m . $monthnum . $year );
		$cache = wp_cache_get( 'get_calendar', 'calendar' );

		if ( $cache && is_array( $cache ) && isset( $cache[ $key ] ) ) {
			/** This filter is documented in wp-includes/general-template.php */
			$output = apply_filters( 'get_calendar', $cache[ $key ] );

			if ( $echo ) {
				echo $output;
				return;
			}

			return $output;
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		// Quick check. If we have no posts at all, abort!
		if ( ! $posts ) {
			$gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 1");
			if ( ! $gotsome ) {
				$cache[ $key ] = '';
				wp_cache_set( 'get_calendar', $cache, 'calendar' );
				return;
			}
		}

		if ( isset( $_GET['w'] ) ) {
			$w = (int) $_GET['w'];
		}
		// week_begins = 0 stands for Sunday
		$week_begins = (int) get_option( 'start_of_week' );
		$ts = current_time( 'timestamp' );

		// Let's figure out when we are
		if ( ! empty( $monthnum ) && ! empty( $year ) ) {
			$thismonth = zeroise( intval( $monthnum ), 2 );
			$thisyear = (int) $year;
		} elseif ( ! empty( $w ) ) {
			// We need to get the month from MySQL
			$thisyear = (int) substr( $m, 0, 4 );
			//it seems MySQL's weeks disagree with PHP's
			$d = ( ( $w - 1 ) * 7 ) + 6;
			$thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
		} elseif ( ! empty( $m ) ) {
			$thisyear = (int) substr( $m, 0, 4 );
			if ( strlen( $m ) < 6 ) {
				$thismonth = '01';
			} else {
				$thismonth = zeroise( (int) substr( $m, 4, 2 ), 2 );
			}
		} else {
			$thisyear = gmdate( 'Y', $ts );
			$thismonth = gmdate( 'm', $ts );
		}

		$unixmonth = mktime( 0, 0 , 0, $thismonth, 1, $thisyear );
		$last_day = date( 't', $unixmonth );

		// Get the next and previous month and year with at least one post
		$previous = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
			FROM $wpdb->posts p JOIN $wpml_icl_table t
			ON p.ID = t.element_id
			AND t.element_type = CONCAT('post_', p.post_type)
			WHERE post_date < '$thisyear-$thismonth-01'
			AND t.language_code = '".ICL_LANGUAGE_CODE."'
			AND post_type = 'post' AND post_status = 'publish'
				ORDER BY post_date DESC
				LIMIT 1");
		$next = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
			FROM $wpdb->posts p JOIN $wpml_icl_table t
			ON p.ID = t.element_id
			AND t.element_type = CONCAT('post_', p.post_type)
			WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
			AND t.language_code = '".ICL_LANGUAGE_CODE."'
			AND post_type = 'post' AND post_status = 'publish'
				ORDER BY post_date ASC
				LIMIT 1");

		/** translators: Calendar caption: 1: month name, 2: 4-digit year */
		$calendar_caption = _x('%1$s %2$s', 'calendar caption');
		$calendar_output = '<table id="wp-calendar">
		<caption>' . sprintf(
			$calendar_caption,
			$wp_locale->get_month( $thismonth ),
			date( 'Y', $unixmonth )
		) . '</caption>
		<thead>
		<tr>';

		$myweek = array();

		for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
			$myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
		}

		foreach ( $myweek as $wd ) {
			$day_name = $initial ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
			$wd = esc_attr( $wd );
			$calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
		}

		$calendar_output .= '
		</tr>
		</thead>

		<tfoot>
		<tr>';

		if ( $previous ) {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . get_month_link( $previous->year, $previous->month ) . '">&laquo; ' .
				$wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) .
			'</a></td>';
		} else {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="prev" class="pad">&nbsp;</td>';
		}

		$calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';

		if ( $next ) {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . get_month_link( $next->year, $next->month ) . '">' .
				$wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) .
			' &raquo;</a></td>';
		} else {
			$calendar_output .= "\n\t\t".'<td colspan="3" id="next" class="pad">&nbsp;</td>';
		}

		$calendar_output .= '
		</tr>
		</tfoot>

		<tbody>
		<tr>';

		$daywithpost = array();

		// Get days with posts
		$dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
			FROM $wpdb->posts p JOIN $wpml_icl_table t
			ON p.ID = t.element_id
			AND t.element_type = CONCAT('post_', p.post_type)
			WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'
			AND t.language_code = '".ICL_LANGUAGE_CODE."'
			AND post_type = 'post' AND post_status = 'publish'
			AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N);
		if ( $dayswithposts ) {
			foreach ( (array) $dayswithposts as $daywith ) {
				$daywithpost[] = $daywith[0];
			}
		}

		// See how much we should pad in the beginning
		$pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
		if ( 0 != $pad ) {
			$calendar_output .= "\n\t\t".'<td colspan="'. esc_attr( $pad ) .'" class="pad">&nbsp;</td>';
		}

		$newrow = false;
		$daysinmonth = (int) date( 't', $unixmonth );

		for ( $day = 1; $day <= $daysinmonth; ++$day ) {
			if ( isset($newrow) && $newrow ) {
				$calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
			}
			$newrow = false;

			if ( $day == gmdate( 'j', $ts ) &&
				$thismonth == gmdate( 'm', $ts ) &&
				$thisyear == gmdate( 'Y', $ts ) ) {
				$calendar_output .= '<td id="today">';
			} else {
				$calendar_output .= '<td>';
			}

			if ( in_array( $day, $daywithpost ) ) {
				// any posts today?
				$date_format = date( _x( 'F j, Y', 'daily archives date format' ), strtotime( "{$thisyear}-{$thismonth}-{$day}" ) );
				$label = sprintf( __( 'Posts published on %s' ), $date_format );
				$calendar_output .= sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					get_day_link( $thisyear, $thismonth, $day ),
					esc_attr( $label ),
					$day
				);
			} else {
				$calendar_output .= $day;
			}
			$calendar_output .= '</td>';

			if ( 6 == calendar_week_mod( date( 'w', mktime(0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
				$newrow = true;
			}
		}

		$pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins );
		if ( $pad != 0 && $pad != 7 ) {
			$calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr( $pad ) .'">&nbsp;</td>';
		}
		$calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

		$cache[ $key ] = $calendar_output;
		wp_cache_set( 'get_calendar', $cache, 'calendar' );

		if ( $echo ) {
			/**
	 * Filter the HTML calendar output.
	 *
	 * @since 3.0.0
	 *
	 * @param string $calendar_output HTML output of the calendar.
	 */
			echo apply_filters( 'get_calendar', $calendar_output );
			return;
		}
		/** This filter is documented in wp-includes/general-template.php */
		return apply_filters( 'get_calendar', $calendar_output );

	} // End wpml_get_calendar

} // End wpml_calendar_widget_init

add_action( 'plugins_loaded', 'wpml_calendar_widget_init' );
