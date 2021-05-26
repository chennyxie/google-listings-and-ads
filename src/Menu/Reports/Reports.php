<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Menu\Reports;

use Automattic\WooCommerce\Admin\Features\Features;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Registerable;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;

/**
 * Class Reports
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Menu\Reports
 */
class Reports implements Service, Registerable {

	/**
	 * Register a service.
	 */
	public function register(): void {
		add_action(
			'admin_menu',
			function() {
				if (
					apply_filters( 'gla_enable_reports', false ) &&
					Features::is_enabled( 'navigation' )
				) {
					$this->register_navigation_pages();
				}
			}
		);
	}

	/**
	 * Register navigation pages for WC Navigation.
	 */
	protected function register_navigation_pages(): void {
		wc_admin_register_page(
			[
				'title'    => __( 'Reports', 'google-listings-and-ads' ),
				'parent'   => 'google-listings-and-ads-category',
				'path'     => '/google/reports/programs',
				'id'       => 'google-reports',
				'nav_args' => [
					'order'  => 20,
					'parent' => 'google-listings-and-ads-category',
				],
			]
		);
	}
}