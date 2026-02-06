<?php
class StmZoom {
	/**
	 * @return StmZoom constructor.
	 */
	protected array $meeting_shortcode_sql = array(
		'where' => '',
		'join'  => '',
	);

	public function __construct() {
		register_activation_hook( STM_ZOOM_FILE, array( $this, 'plugin_activation_hook' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );
		add_action( 'wp_head', array( $this, 'head' ) );
		add_shortcode( 'stm_zoom_conference', array( $this, 'add_meeting_shortcode' ) );
		add_shortcode( 'stm_zoom_webinar', array( $this, 'add_webinar_shortcode' ) );
		add_shortcode( 'stm_zoom_conference_grid', array( $this, 'add_meeting_grid_shortcode' ) );
		add_filter( 'template_include', array( $this, 'single_zoom_template' ), 200 );
	}

	/**
	 * Plugin Activation Hook
	 */
	public function plugin_activation_hook() {
		if ( empty( get_option( 'eroom_installed' ) ) ) {
			update_option( 'eroom_installed', time() );
		}
	}

	/**
	 * Load Single Meeting or Webinar Template
	 * @param $template
	 * @return bool|string
	 */
	public function single_zoom_template( $template ) {
		global $post;

		if ( isset( $post->post_type ) && in_array( $post->post_type, array( 'stm-zoom', 'stm-zoom-webinar' ), true ) ) {
			$post_id = get_the_ID();
			if ( ! empty( $_GET['show_meeting'] ) ) { // phpcs:ignore
				$template = get_zoom_template( 'single/meeting_view.php' );
			} elseif ( ! empty( $_GET['ical_export'] ) ) { // phpcs:ignore
				header( 'Content-type: text/calendar; charset=utf-8' );
				header( 'Content-Disposition: inline; filename=calendar_' . $post_id . '.ics' );
				echo stm_eroom_generate_ics_calendar();//phpcs:ignore
				exit();
			} else {
				$template = get_zoom_template( 'single/main.php' );
			}
		}

		return $template;
	}

	/**
	 * Enqueue Frontend Styles & Scripts
	 */
	public function frontend_enqueue() {
		wp_enqueue_script( 'stm_jquery.countdown', STM_ZOOM_URL . 'assets/js/frontend/jquery.countdown.js', array( 'jquery' ), STM_ZOOM_VERSION, true );
		wp_enqueue_script( 'stm_zoom_main', STM_ZOOM_URL . 'assets/js/frontend/main.js', array( 'jquery' ), STM_ZOOM_VERSION, true );
		wp_enqueue_style( 'stm_zoom_main', STM_ZOOM_URL . 'assets/css/frontend/main.css', false, STM_ZOOM_VERSION );
	}

	/**
	 * Define Frontend Translation Variables
	 */
	public function head() {
		?>
		<script>
			var daysStr = "<?php esc_html_e( 'Days', 'eroom-zoom-meetings-webinar' ); ?>";
			var hoursStr = "<?php esc_html_e( 'Hours', 'eroom-zoom-meetings-webinar' ); ?>";
			var minutesStr = "<?php esc_html_e( 'Minutes', 'eroom-zoom-meetings-webinar' ); ?>";
			var secondsStr = "<?php esc_html_e( 'Seconds', 'eroom-zoom-meetings-webinar' ); ?>";
		</script>
		<?php
	}

	/**
	 * Add Meeting Shortcode
	 * @param $atts
	 * @return string
	 */
	public function add_meeting_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'post_id'                   => '',
				'hide_content_before_start' => '',
			),
			$atts
		);

		$content                   = '';
		$hide_content_before_start = '';
		if ( ! empty( $atts['hide_content_before_start'] ) ) {
			$hide_content_before_start = '1';
		}
		if ( ! empty( $atts['post_id'] ) ) {
			$content = self::add_zoom_content( $atts['post_id'], $hide_content_before_start );
		}
		return $content;
	}

	/**
	 * Add Webinar Shortcode
	 * @param $atts
	 * @return string
	 */
	public function add_webinar_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'post_id'                   => '',
				'hide_content_before_start' => '',
			),
			$atts
		);

		$content                   = '';
		$hide_content_before_start = '';
		if ( ! empty( $atts['hide_content_before_start'] ) ) {
			$hide_content_before_start = '1';
		}
		if ( ! empty( $atts['post_id'] ) ) {
			$content = self::add_zoom_content( $atts['post_id'], $hide_content_before_start, true );
		}
		return $content;
	}

	/**
	 * Add Meetings Grid Shortcode
	 * @param $atts
	 * @return string
	 */
	public function add_meeting_grid_shortcode( $atts ) {
		global $wpdb;

		$this->meeting_shortcode_sql = array(
			'where' => '',
			'join'  => '',
		);

		$meta_sql = array();

		$atts = shortcode_atts(
			array(
				'count'     => '3',
				'per_row'   => '',
				'category'  => '',
				'recurring' => '',
				'post_type' => 'stm-zoom',
				'order'     => 'DESC',
				'orderby'   => 'date',
				'meta_key'  => '',
				'meta_type' => '',
			),
			$atts
		);

		$count     = intval( $atts['count'] );
		$per_row   = ! empty( $atts['per_row'] ) ? intval( $atts['per_row'] ) : '';
		$recurring = ! empty( $atts['recurring'] );
		$order     = in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $atts['order'] ) : 'DESC';

		$valid_orderby = array( 'none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'relevance', 'menu_order', 'meta_value', 'meta_value_num', 'post__in', 'post_name__in', 'post_parent__in' );
		$orderby       = in_array( $atts['orderby'], $valid_orderby, true ) ? $atts['orderby'] : 'date';

		$meta_key = sanitize_key( $atts['meta_key'] );

		$valid_meta_types = array( 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );
		$meta_type        = in_array( strtoupper( $atts['meta_type'] ), $valid_meta_types, true ) ? strtoupper( $atts['meta_type'] ) : '';

		//clear all spaces
		$post_type = preg_replace( '/\s+/', '', $atts['post_type'] );
		$post_type = explode( ',', $post_type );

		//all post types
		$post_types = array( 'stm-zoom', 'stm-zoom-webinar', 'product' );

		//filter post type
		$post_type = array_filter(
			$post_type,
			function ( $item ) use ( $post_types ) {
				return in_array( $item, $post_types, true );
			}
		);

		$exclude_ids = array();
		if ( ! class_exists( 'StmZoomPro' ) ) {
			//delete product, If not Pro
			$key = array_search( 'product', $post_type, true );
			if ( false !== $key ) {
				unset( $post_type[ $key ] );
			}
		} elseif ( in_array( 'product', $post_type, true ) ) {
			//remove meeting if has in product - only when product is requested
			$option_ids  = get_option( 'stm_wc_product_meeting_ids', array() );
			$exclude_ids = array_keys( $option_ids );
		}

		$args = array(
			'posts_per_page' => $count,
			'post_type'      => $post_type,
			'post__not_in'   => $exclude_ids,
		);

		if ( ! empty( $atts['order'] ) ) {
			$args['order'] = $order;
		}

		if ( ! empty( $atts['orderby'] ) ) {
			$args['orderby'] = $orderby;
		}

		if ( ! empty( $meta_key ) ) {
			$args['meta_key'] = $meta_key;
		}

		if ( ! empty( $meta_type ) ) {
			$args['meta_type'] = $meta_type;
		}

		$sql_post_types = array_map(
			function ( $item ) {
				return "'" . $item . "'";
			},
			$post_type
		);

		if ( count( $sql_post_types ) ) {
			$this->meeting_shortcode_sql['where'] .= ' AND ' . $wpdb->prefix . 'posts.post_type IN (' . implode( ', ', $sql_post_types ) . ')';
		}

		if ( count( $exclude_ids ) ) {
			$this->meeting_shortcode_sql['where'] .= ' AND ' . $wpdb->prefix . 'posts.ID NOT IN (' . implode( ', ', $exclude_ids ) . ')';
		}

		$this->meeting_shortcode_sql['join'] = ' LEFT JOIN ' . $wpdb->prefix . 'postmeta AS pm1 ON ' . $wpdb->prefix . 'posts.ID = pm1.post_id';

		if ( $recurring ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'stm_recurring_enabled',
					'value'   => 'on',
					'compare' => '=',
				),
			);

			$meta_sql[] = "(pm1.meta_key = 'stm_recurring_enabled' AND pm1.meta_value = 'on')";

			if ( in_array( 'product', $post_type, true ) ) {
				$option_recurring_ids           = get_option( 'stm_recurring_meeting_ids', array() );
				$args['meta_query']['relation'] = 'OR';
				$args['meta_query'][]           = array(
					'key'     => '_meeting_id',
					'value'   => $option_recurring_ids,
					'compare' => 'IN',
				);
				$meta_sql[]                     = 'OR';
				$meta_sql[]                     = '(pm1.meta_key = "_meeting_id" AND pm1.meta_value IN (' . implode( ', ', $option_recurring_ids ) . '))';
				$meta_sql                       = array(
					'(' . implode( ' ', $meta_sql ) . ')',
				);
			}
		} elseif ( in_array( 'product', $post_type, true ) && count( $post_type ) === 1 ) {
			// Only apply product meta filter when querying products alone.
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_meeting_id',
					'value'   => '',
					'compare' => '!=',
				),
				array(
					'key'     => 'stm_waiting_room',
					'compare' => 'EXISTS',
				),
			);

			$meta_sql[] = "((pm1.meta_key = '_meeting_id' AND pm1.meta_value != '') OR (pm1.meta_key = 'stm_waiting_room'))";
		}

		if ( ! empty( $atts['category'] ) ) {
			$category = preg_replace( '/\s+/', '', $atts['category'] );
			$category = explode( ',', $category );

			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $category,
					'operator' => 'IN',
				),
			);

			$this->meeting_shortcode_sql['join']  .= ' LEFT JOIN ON ' . $wpdb->prefix . 'term_relationships AS tr1 ON ' . $wpdb->prefix . 'posts.ID = tr1.object_id';
			$this->meeting_shortcode_sql['where'] .= ' AND tr1.term_taxonomy_id IN (' . implode( ', ', $category ) . ')';
		}

		$args['meta_query'][] = array(
			'relation' => 'OR',
			array(
				'key'     => 'stm_select_gm_zoom',
				'value'   => 'zoom',
				'compare' => '=',
			),
			array(
				'key'     => 'stm_select_gm_zoom',
				'compare' => 'NOT EXISTS',
			),
		);

		$meta_sql[] = '((pm1.meta_key = "stm_select_gm_zoom" AND pm1.meta_value = "zoom") OR NOT EXISTS ( SELECT mt3.post_id FROM ' . $wpdb->prefix . 'postmeta AS mt3 WHERE mt3.post_id = ' . $wpdb->prefix . 'posts.ID AND mt3.meta_key = "stm_select_gm_zoom" ))';

		$this->meeting_shortcode_sql['where'] .= ' AND (' . implode( ' AND ', $meta_sql ) . ')';

		ob_start();

		add_filter( 'posts_where', array( $this, 'posts_where' ), 10 );
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10 );

		$q = new WP_Query( $args );

		remove_filter( 'posts_where', array( $this, 'posts_where' ), 10 );
		remove_filter( 'posts_join', array( $this, 'posts_join' ), 10 );

		if ( $q->have_posts() ) {
			$users = self::stm_zoom_get_users();
			?>
			<div class="stm_zoom_grid_container">
				<div class="stm_zoom_grid per_row_<?php echo esc_attr( $per_row ); ?>">
					<?php
					while ( $q->have_posts() ) {
						$q->the_post();
						$path = get_zoom_template( 'loop/single-meeting.php' );
						include $path;
					}
					?>
				</div>
			</div>
			<?php
		}

		wp_reset_postdata();

		$content = ob_get_clean();

		return $content;
	}

	public function posts_where( $where ) {
		return $where . $this->meeting_shortcode_sql['where'];
	}

	public function posts_join( $join ) {
		return $join . $this->meeting_shortcode_sql['join'];
	}

	/**
	 * Zoom Meeting Content
	 * @param $post_id
	 * @param string $hide_content_before_start
	 * @return string
	 */
	public static function add_zoom_content( $post_id, $hide_content_before_start = '', $webinar = false ) {
		$content = '';
		if ( ! empty( $post_id ) ) {
			$post_id      = intval( $post_id );
			$meeting_data = self::meeting_time_data( $post_id );
			if ( ! empty( $meeting_data ) && ! empty( $meeting_data['meeting_start'] ) && ! empty( $meeting_data['meeting_date'] ) ) {
				$meeting_start = $meeting_data['meeting_start'];
				$meeting_date  = $meeting_data['meeting_date'];
				$is_started    = $meeting_data['is_started'];

				$zoom_data      = get_post_meta( $post_id, 'stm_zoom_data', true );
				$recurring_data = array();

				if ( class_exists( 'StmZoomRecurring' ) ) {
					$recurring_data = StmZoomRecurring::stm_product_recurring_meeting_data( $post_id, $zoom_data );

					//no fixed time
					if ( isset( $zoom_data['type'] ) && in_array( strval( $zoom_data['type'] ), StmZoomAPITypes::TYPES_NO_FIXED, true ) ) {
						$is_started = true;
					}
				}

				if ( ! $is_started ) {
					$content = self::countdown( $meeting_date, false, $webinar );
				} elseif ( isset( $recurring_data['next_meeting_start'] ) ) {
					$content = self::countdown( $recurring_data['next_meeting_start'], false, $webinar );
				} else {
					$hide_content_before_start = '';
				}

				if ( empty( $hide_content_before_start ) ) {
					$content .= self::zoom_content( $post_id, $meeting_start, $webinar, $zoom_data, $recurring_data );
				}
			}
		}
		return '<div class="stm_zoom_wrapper">' . $content . '</div>';
	}

	/**
	 * Collect Meeting Data
	 * @param $post_id
	 * @return array|bool
	 */
	public static function meeting_time_data( $post_id ) {
		if ( empty( $post_id ) ) {
			return false;
		}

		$has_post = get_post_status( $post_id );
		if ( empty( $has_post ) ) {
			return false;
		}

		$r        = array();
		$post_id  = intval( $post_id );
		$provider = get_post_meta( $post_id, 'stm_select_gm_zoom', true );
		if ( empty( $provider ) || 'zoom' === $provider ) {
			$start_date = get_post_meta( $post_id, 'stm_date', true );
			$start_time = get_post_meta( $post_id, 'stm_time', true );
		} else {
			$start_date = get_post_meta( $post_id, 'stm_start_date', true );
			$start_time = get_post_meta( $post_id, 'stm_start_time', true );
		}
		$timezone      = get_post_meta( $post_id, 'stm_timezone', true );
		$meeting_start = strtotime( 'today', ( apply_filters( 'eroom_sanitize_stm_date', $start_date ) / 1000 ) );

		if ( ! empty( $start_time ) ) {
			$time = explode( ':', $start_time );
			if ( is_array( $time ) && count( $time ) === 2 ) {
				$meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
			}
		}

		$meeting_start = date( 'Y-m-d H:i:s', $meeting_start ); //phpcs:ignore
		if ( empty( $timezone ) ) {
			$timezone = 'UTC';
		}

		$meeting_date = new DateTime( $meeting_start, new DateTimeZone( $timezone ) );
		$meeting_date = $meeting_date->format( 'U' );
		$is_started   = ! ( $meeting_date > time() );

		$r['meeting_start'] = $meeting_start;
		$r['meeting_date']  = $meeting_date;
		$r['is_started']    = $is_started;

		return $r;
	}

	/**
	 * Meeting Countdown
	 * @param string $time
	 * @param bool $hide_title
	 * @return string
	 */
	public static function countdown( $time = '', $hide_title = false, $webinar = false ) {
		if ( ! empty( $time ) ) {
			$countdown = '<div class="zoom_countdown_wrap">';
			if ( ! $hide_title ) {
				$title      = ( $webinar ) ? esc_html__( 'Webinar starts in', 'eroom-zoom-meetings-webinar' ) : esc_html__( 'Meeting starts in', 'eroom-zoom-meetings-webinar' );
				$countdown .= '<h2 class="countdown_title">' . $title . '</h2>';
			}
			$countdown .= '<div class="stm_zooom_countdown" data-timer="' . esc_attr( $time ) . '"></div></div>';

			return $countdown;
		}
	}

	/**
	 * Zoom Meeting Content Template
	 *
	 * @param $post_id
	 * @param $meeting_start
	 * @param $webinar
	 * @param $zoom_data
	 * @param $recurring_data
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function zoom_content( $post_id, $meeting_start, $webinar = false, $zoom_data = false, $recurring_data = false ) {
		global $post;

		if ( ! empty( $post_id ) && ! empty( $zoom_data ) && ! empty( $zoom_data['id'] ) ) {
			$meeting_id  = sanitize_text_field( $zoom_data['id'] );
			$title       = get_the_title( $post_id );
			$agenda      = get_post_meta( $post_id, 'stm_agenda', true );
			$password    = get_post_meta( $post_id, 'stm_password', true );
			$duration    = get_post_meta( $post_id, 'stm_duration', true );
			$start_time  = get_post_meta( $post_id, 'stm_time', true );
			$time_zone   = get_post_meta( $post_id, 'stm_timezone', true );
			$option_ids  = get_option( 'stm_wc_product_meeting_ids', array() );
			$exclude_ids = array_keys( $option_ids );
			$description = self::build_calendar_description( $meeting_id, $zoom_data, $title, $agenda, $password );

			$config_calendar = array(
				'start'       => $meeting_start,
				'allDay'      => isset( $zoom_data['type'] ) && in_array( strval( $zoom_data['type'] ), StmZoomAPITypes::TYPES_NO_FIXED, true ),
				'address'     => '',
				'title'       => $title,
				'duration'    => $duration,
				'description' => $description,
				'start_time'  => $start_time,
				'timezone'    => $time_zone,
			);

			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			ob_start();

			if ( 'stm-zoom' === $post->post_type ) {
				the_content();
			}
			?>
			<div class="stm_zoom_content">
				<?php if ( has_post_thumbnail( $post_id ) ) { ?>
					<div class="zoom_image">
						<?php echo get_the_post_thumbnail( $post_id, 'large' ); ?>
					</div>
				<?php } ?>
				<div class="zoom_content">
					<div class="zoom_info">
						<h2><?php echo esc_html( $title ); ?></h2>
						<?php if ( isset( $zoom_data['type'] ) && in_array( strval( $zoom_data['type'] ), StmZoomAPITypes::TYPES_NO_FIXED, true ) ) { ?>
							<div class="zoom-recurring-no_fixed_time"><?php esc_html_e( 'No fixed time', 'eroom-zoom-meetings-webinar' ); ?></div>
						<?php } elseif ( isset( $zoom_data['type'] ) && in_array( strval( $zoom_data['type'] ), StmZoomAPITypes::TYPES_RECURRING, true ) && ! empty( $recurring_data ) ) { ?>
							<div class="zoom-recurring">
								<?php if ( isset( $recurring_data['start_date'] ) ) : ?>
									<div class="zoom-recurring__from">
										<span class="zoom-recurring--title"><?php esc_html_e( 'From:', 'eroom-zoom-meetings-webinar' ); ?></span>
										<span class="zoom-recurring--content"><?php echo esc_html( date_i18n( $date_format . ' ' . $time_format, $recurring_data['next_meeting_start'] ) ); ?></span>
									</div>
								<?php endif; ?>
								<?php if ( isset( $recurring_data['end_date'] ) ) : ?>
									<div class="zoom-recurring__to">
										<span class="zoom-recurring--title"><?php esc_html_e( 'To:', 'eroom-zoom-meetings-webinar' ); ?></span>
										<span class="zoom-recurring--content"><?php echo esc_html( date_i18n( $date_format . ' ' . $time_format, $recurring_data['end_meeting_date'] ) ); ?></span>
									</div>
								<?php endif; ?>
								<?php if ( isset( $recurring_data['repeat_interval'] ) ) : ?>
									<div class="zoom-recurring__interval">
										<span class="zoom-recurring--title">
											<?php ( $webinar ) ? esc_html_e( 'Webinar recurrence:', 'eroom-zoom-meetings-webinar' ) : esc_html_e( 'Meeting recurrence:', 'eroom-zoom-meetings-webinar' ); ?>
										</span>
									<span class="zoom-recurring--content"><?php echo esc_html( $recurring_data['repeat_interval'] ); ?></span>
									</div>
								<?php endif; ?>
							</div>
						<?php } elseif ( ! empty( $meeting_start ) ) { ?>
							<div class="date">
								<span><?php ( $webinar ) ? esc_html_e( 'Webinar date', 'eroom-zoom-meetings-webinar' ) : esc_html_e( 'Meeting date', 'eroom-zoom-meetings-webinar' ); ?> </span>
								<?php
								$date            = new DateTime( $meeting_start, new DateTimeZone( $time_zone ) );
								$formatted_date  = $date->format( 'F j, Y g:i A' );
								$timezone_offset = $date->format( 'P' );
								$timezone_display = TimezoneHelper::get_display( $time_zone, $timezone_offset );

								// Build the complete date and timezone HTML
								$date_timezone_html = sprintf(
									'<b>%s<span class="timezone">%s</span></b>',
									esc_html( $formatted_date ) . "\n\t\t\t\t\t\t\t\t\t",
									esc_html( $timezone_display )
								);

								/**
								 * Filter the complete date and timezone display HTML
								 *
								 * @param string $date_timezone_html The complete HTML for date and timezone display
								 * @param string $formatted_date     The formatted date string (e.g., "December 27, 2025 10:28 PM")
								 * @param string $timezone_display   The timezone display string (e.g., "(GMT-05:00) EST")
								 * @param int    $post_id           The meeting/webinar post ID
								 * @param object $date              DateTime object for the meeting start time
								 * @param string $time_zone         The timezone identifier (e.g., "America/New_York")
								 * @param bool   $webinar           Whether this is a webinar (true) or meeting (false)
								 */
								echo apply_filters(
									'eroom_date_timezone_display',
									$date_timezone_html,
									$formatted_date,
									$timezone_display,
									$post_id,
									$date,
									$time_zone,
									$webinar
								);
								?>
							</div>
						<?php } ?>
						<div class="stm-calendar-links">
							<span><?php echo esc_html__( 'Add to:', 'eroom-zoom-meetings-webinar' ); ?></span>
							<a href="<?php echo esc_url( stm_eroom_generate_google_calendar( $config_calendar, $recurring_data ) ); ?>" target="_blank"><?php echo esc_html__( 'Google Calendar', 'eroom-zoom-meetings-webinar' ); ?></a>
							,
							<a href="<?php echo esc_attr( add_query_arg( array( 'ical_export' => '1' ), get_permalink( $post_id ) ) ); ?>
								" class="" target="_blank">
								<?php echo esc_html__( 'iCal Export', 'eroom-zoom-meetings-webinar' ); ?>
							</a>
						</div>
						<?php if ( ! in_array( $post_id, $exclude_ids, true ) ) : ?>
							<?php if ( ! empty( $password ) ) { ?>
								<div class="password">
									<span><?php esc_html_e( 'Password: ', 'eroom-zoom-meetings-webinar' ); ?></span>
									<span class="value"><?php echo esc_html( $password ); ?></span>
								</div>
							<?php } ?>
							<a href="
							<?php echo esc_attr( add_query_arg( array( 'show_meeting' => '1' ), get_permalink( $post_id ) ) ); ?>
								" class="btn stm-join-btn join_in_menu" target="_blank">
								<?php esc_html_e( 'Join in browser', 'eroom-zoom-meetings-webinar' ); ?>
							</a>
							<?php
							$hide_zoom_app_button = get_post_meta( $post_id, 'stm_hide_zoom_app_button', true );
							if ( empty( $hide_zoom_app_button ) ) :
							?>
							<a href="https://zoom.us/j/<?php echo esc_attr( $meeting_id ); ?>" class="btn stm-join-btn outline" target="_blank">
								<?php esc_html_e( 'Join in zoom app', 'eroom-zoom-meetings-webinar' ); ?>
							</a>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
				<div class="zoom_description">
					<?php if ( ! empty( $agenda ) ) { ?>
						<div class="agenda">
							<?php echo wp_kses_post( $agenda ); ?>
						</div>
					<?php } ?>
					<div id="zmmtg-root"></div>
					<div id="aria-notify-area"></div>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}
	}

	public static function stm_zoom_get_users_list( $options = array() ) {

		$users_list = array();
		$users_data = new \Zoom\Endpoint\Users();
		$users_list = $users_data->userlist( $options );

		return $users_list;
	}

	/**
	 * Get Zoom Users from Zoom API
	 * @return array
	 */
	public static function stm_zoom_get_users() {
		$users = get_transient( 'stm_zoom_users' );

		if ( empty( $users ) ) {
			$users_list = self::stm_zoom_get_users_list( array( 'page_size' => 300 ) );

			// Check if the API returned an error (e.g., missing scopes).
			if ( ! empty( $users_list ) && isset( $users_list['code'] ) && isset( $users_list['message'] ) ) {
				set_transient( 'stm_eroom_api_error', $users_list['message'], DAY_IN_SECONDS );
				return array();
			}
			
			if ( ! empty( $users_list ) && ! empty( $users_list['users'] ) ) {
				$users = $users_list['users'];
				set_transient( 'stm_zoom_users', $users, 36000 );
			}
		}

		// Clear any previous error if the API call succeeded.
		delete_transient( 'stm_eroom_api_error' );

		return $users;
	}

	/**
	 * Get Zoom Users from Zoom API
	 *
	 * @param int $page_number
	 *
	 * @return array
	 */
	public static function stm_zoom_get_users_pagination( $page_number = 1 ) {
		$options = array(
			'page_size' => 300,
		);

		return self::stm_zoom_get_users_list( $options );
	}

	/**
	 * Get Zoom Users
	 * @return array
	 */
	public static function get_users_options() {
		$users = self::stm_zoom_get_users();
		$user_list = array(
			'' => esc_html__( '— Select Meeting Host —', 'eroom-zoom-meetings-webinar' ),
		);

		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				$first_name       = $user['first_name'];
				$last_name        = $user['last_name'];
				$email            = $user['email'];
				$id               = $user['id'];
				$user_list[ $id ] = $first_name . ' ' . $last_name . ' (' . $email . ')';
			}
		}

		return $user_list;
	}

	/**
	 * Get Users for Autocomplete
	 * @return array
	 */
	public static function get_autocomplete_users_options() {
		$users  = self::get_users_options();
		$result = array();
		foreach ( $users as $id => $user ) {
			$result[] = array(
				'id'        => $id,
				'title'     => $user,
				'post_type' => '',
			);
		}
		return $result;
	}

	/**
	 * Build calendar description with Zoom invitation
	 *
	 * @param string $meeting_id
	 * @param array  $zoom_data
	 * @param string $title
	 * @param string $agenda
	 * @param string $password
	 *
	 * @return string
	 */
	public static function build_calendar_description( $meeting_id, $zoom_data, $title, $agenda, $password ) {
		// Sanitize and validate input parameters
		$meeting_id = ! empty( $meeting_id ) ? sanitize_text_field( $meeting_id ) : '';
		$zoom_data  = is_array( $zoom_data ) ? $zoom_data : array();
		$title      = ! empty( $title ) ? sanitize_text_field( $title ) : esc_html__( 'Zoom Meeting', 'eroom-zoom-meetings-webinar' );
		$agenda     = ! empty( $agenda ) ? wp_kses_post( $agenda ) : '';
		$password   = ! empty( $password ) ? sanitize_text_field( $password ) : '';

		$description        = '';
		$invitation_fetched = false;

		// Add agenda if available
		if ( ! empty( $agenda ) ) {
			$description .= $agenda . '<br><br>';
			$description .= '---<br><br>';
		}

		// Try to fetch invitation from Zoom API only if meeting_id is valid
		if ( ! empty( $meeting_id ) ) {
			try {
				// Check if Zoom endpoint class exists and has invitation method
				if ( class_exists( '\Zoom\Endpoint\Meetings' ) ) {
					$zoom_endpoint = new \Zoom\Endpoint\Meetings();

					// Verify the invitation method exists before calling
					if ( method_exists( $zoom_endpoint, 'invitation' ) ) {
						$invitation = $zoom_endpoint->invitation( $meeting_id );

						if ( is_array( $invitation ) && ! empty( $invitation['invitation'] ) ) {
							$description       .= str_replace( "\n", '<br>', $invitation['invitation'] );
							$invitation_fetched = true;
						} elseif ( is_array( $invitation ) && ! empty( $invitation['code'] ) && 4711 === $invitation['code'] ) {
							set_transient( 'stm_zoom_missing_invitation_scope', true, DAY_IN_SECONDS );
						}
					} else {
						error_log( 'Zoom Endpoint Meetings::invitation() method not found' );
					}
				} else {
					error_log( 'Zoom Endpoint Meetings class not found' );
				}
			} catch ( \Exception $e ) {
				error_log( 'Zoom invitation API error: ' . $e->getMessage() );
			} catch ( \Error $e ) {
				error_log( 'Zoom invitation fatal error: ' . $e->getMessage() );
			} catch ( \Throwable $e ) {
				error_log( 'Zoom invitation unexpected error: ' . $e->getMessage() );
			}
		}

		// Fallback: Build basic invitation if API fetch failed or returned empty
		if ( ! $invitation_fetched ) {
			$meeting_link = '';

			// Try to get join_url from zoom_data
			if ( ! empty( $zoom_data['join_url'] ) ) {
				$meeting_link = esc_url( $zoom_data['join_url'] );
			} elseif ( ! empty( $meeting_id ) ) {
				$meeting_link = 'https://zoom.us/j/' . $meeting_id;
			}

			if ( ! empty( $meeting_link ) ) {
				$description .= $title . ' ' . esc_html__( 'is inviting you to a scheduled Zoom meeting.', 'eroom-zoom-meetings-webinar' ) . '<br><br>';
				$description .= esc_html__( 'Join Zoom Meeting', 'eroom-zoom-meetings-webinar' ) . '<br>';
				$description .= $meeting_link . '<br><br>';

				if ( ! empty( $meeting_id ) ) {
					$description .= esc_html__( 'Meeting ID: ', 'eroom-zoom-meetings-webinar' ) . $meeting_id . '<br>';
				}

				if ( ! empty( $password ) ) {
					$description .= esc_html__( 'Passcode: ', 'eroom-zoom-meetings-webinar' ) . $password . '<br>';
				}
			} else {
				// Ultimate fallback if no meeting information is available
				$description .= $title . '<br>';
				$description .= esc_html__( 'Zoom meeting details will be available soon.', 'eroom-zoom-meetings-webinar' );
			}
		}

		return ! empty( $description ) ? $description : $title;
	}
}
