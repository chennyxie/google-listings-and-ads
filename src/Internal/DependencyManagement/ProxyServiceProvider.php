<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Internal\DependencyManagement;

use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\Http;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\RESTServer;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\Tracks;

/**
 * Class ProxyServiceProvider
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Internal\DependencyManagement
 */
class ProxyServiceProvider extends AbstractServiceProvider {

	/**
	 * Array of classes provided by this container.
	 *
	 * @var array
	 */
	protected $provides = [
		Http::class       => true,
		RESTServer::class => true,
		Tracks::class     => true,
	];

	/**
	 * Use the register method to register items with the container via the
	 * protected $this->leagueContainer property or the `getLeagueContainer` method
	 * from the ContainerAwareTrait.
	 *
	 * @return void
	 */
	public function register() {
		// The Http class should be a new object every time.
		$this->add( Http::class );

		// Our other classes can be shared like normal.
		$this->share( RESTServer::class );
		$this->share( Tracks::class );
	}
}