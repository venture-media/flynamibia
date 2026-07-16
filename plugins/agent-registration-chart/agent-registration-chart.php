<?php
/**
 * Plugin Name: Agent Registration Chart
 * Description: Shortcode [agent_registration_chart] — bar chart of agent signups for the past 12 months (Chart.js).
 * Version:     0.9.0
 * Author:      Leon de Klerk
 * Author URI:	https://github.com/Leon2332
 * Text Domain: agent-registration-chart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Count agents (agent + pending_agent) registered per month for the past 12 months.
 *
 * @return array{labels: string[], counts: int[]}
 */
function arc_get_monthly_registration_data() {
	$tz  = wp_timezone();
	$now = new DateTimeImmutable( 'now', $tz );

	// First day of the month 11 months ago → through the current month (12 months total).
	$start = $now->modify( 'first day of this month' )->setTime( 0, 0, 0 )->modify( '-11 months' );
	$end   = $now->modify( 'last day of this month' )->setTime( 23, 59, 59 );

	$months = array();
	for ( $i = 0; $i < 12; $i++ ) {
		$month              = $start->modify( "+{$i} months" );
		$key                = $month->format( 'Y-m' );
		$months[ $key ]     = array(
			'label' => $month->format( 'M y' ), // e.g. Jul 25
			'count' => 0,
		);
	}

	$users = get_users(
		array(
			'role__in'   => array( 'agent', 'pending_agent' ),
			'number'     => -1,
			'fields'     => array( 'ID', 'user_registered' ),
			'date_query' => array(
				array(
					'after'     => $start->format( 'Y-m-d H:i:s' ),
					'before'    => $end->format( 'Y-m-d H:i:s' ),
					'inclusive' => true,
					'column'    => 'user_registered',
				),
			),
		)
	);

	foreach ( $users as $user ) {
		try {
			$registered = new DateTimeImmutable( $user->user_registered, $tz );
		} catch ( Exception $e ) {
			continue;
		}

		$key = $registered->format( 'Y-m' );
		if ( isset( $months[ $key ] ) ) {
			$months[ $key ]['count']++;
		}
	}

	$labels = array();
	$counts = array();
	foreach ( $months as $month ) {
		$labels[] = $month['label'];
		$counts[] = (int) $month['count'];
	}

	return array(
		'labels' => $labels,
		'counts' => $counts,
	);
}

/**
 * Register Chart.js (loaded only when the shortcode runs).
 */
function arc_register_assets() {
	wp_register_script(
		'chart-js',
		'https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js',
		array(),
		'4.4.7',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'arc_register_assets' );

/**
 * Shortcode: [agent_registration_chart]
 */
function arc_registration_chart_shortcode() {
	if ( ! current_user_can( 'administrator' ) ) {
		return '<p>You do not have permission to view this chart.</p>';
	}

	wp_enqueue_script( 'chart-js' );

	$data      = arc_get_monthly_registration_data();
	$canvas_id = 'arc-chart-' . wp_unique_id();

	$chart_config = array(
		'type'    => 'bar',
		'data'    => array(
			'labels'   => $data['labels'],
			'datasets' => array(
				array(
					'data'            => $data['counts'],
					'backgroundColor' => '#ab292e',
					'borderColor'     => '#ab292e',
					'borderWidth'     => 0,
				),
			),
		),
		'options' => array(
			'responsive'          => true,
			'maintainAspectRatio' => true,
			'plugins'             => array(
				'legend' => array(
					'display' => false,
				),
				'title'  => array(
					'display' => false,
				),
			),
			'scales'              => array(
				'y' => array(
					'beginAtZero' => true,
					'ticks'       => array(
						'precision' => 0,
						'stepSize'  => 1,
					),
				),
			),
		),
	);

	$init_js = sprintf(
		'(function(){var id=%s,cfg=%s;function run(){if(typeof Chart==="undefined"){return;}var el=document.getElementById(id);if(!el||el.dataset.arcReady){return;}el.dataset.arcReady="1";new Chart(el,cfg);}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",run);}else{run();}})();',
		wp_json_encode( $canvas_id ),
		wp_json_encode( $chart_config )
	);

	wp_add_inline_script( 'chart-js', $init_js );

	return sprintf(
		'<div class="arc-chart-wrap" style="position:relative;max-width:100%%;"><canvas id="%s" aria-label="Agent registrations by month" role="img"></canvas></div>',
		esc_attr( $canvas_id )
	);
}
add_shortcode( 'agent_registration_chart', 'arc_registration_chart_shortcode' );
