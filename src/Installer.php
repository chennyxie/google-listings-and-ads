<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds;

use Automattic\WooCommerce\GoogleListingsAndAds\Exception\ValidateInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Registerable;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;
use Automattic\WooCommerce\GoogleListingsAndAds\Internal\Interfaces\InstallableInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Options\OptionsAwareInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Options\OptionsAwareTrait;
use Automattic\WooCommerce\GoogleListingsAndAds\Options\OptionsInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Installer class.
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds
 */
class Installer implements OptionsAwareInterface, Service, Registerable {

	use OptionsAwareTrait;
	use PluginHelper;
	use ValidateInterface;

	/**
	 * @var InstallableInterface[]
	 */
	protected $installables;

	/**
	 * @var OptionsInterface
	 */
	protected $options;

	/**
	 * Installer constructor.
	 *
	 * @param InstallableInterface[] $installables
	 */
	public function __construct( array $installables ) {
		$this->installables = $installables;
		$this->validate_installables();
	}

	/**
	 * Register a service.
	 */
	public function register(): void {
		add_action(
			'admin_init',
			function() {
				$this->admin_init();
			}
		);
	}

	/**
	 * Admin init.
	 */
	protected function admin_init(): void {
		if ( defined( 'IFRAME_REQUEST' ) || is_ajax() ) {
			return;
		}

		$this->check_if_plugin_files_updated();

		$db_version = $this->get_db_version();
		if ( $db_version !== $this->get_version() ) {
			$this->install();

			if ( '' === $db_version ) {
				$this->first_install();
			}

			$this->options->update( OptionsInterface::DB_VERSION, $this->get_version() );
		}
	}

	/**
	 * Install GLA.
	 *
	 * Run on every plugin update.
	 */
	protected function install(): void {
		foreach ( $this->installables as $installable ) {
			$installable->install();
		}
	}

	/**
	 * Checks and records if plugin files have been updated.
	 */
	protected function check_if_plugin_files_updated(): void {
		if ( $this->get_file_version() !== $this->get_version() ) {
			$this->options->update( OptionsInterface::FILE_VERSION, $this->get_version() );
		}
	}

	/**
	 * Runs on the first install of GLA.
	 */
	protected function first_install(): void {
		// Use add here to avoid overwriting the value if somehow happens to already be set
		$this->options->add( OptionsInterface::INSTALL_TIMESTAMP, time() );
	}

	/**
	 * Get the db version
	 *
	 * @return string
	 */
	protected function get_db_version(): string {
		return $this->options->get( OptionsInterface::DB_VERSION, '' );
	}

	/**
	 * Get the stored file version
	 *
	 * @return string
	 */
	protected function get_file_version(): string {
		return $this->options->get( OptionsInterface::FILE_VERSION, '' );
	}

	/**
	 * Validate that each of the installable items is of the correct interface.
	 */
	protected function validate_installables() {
		foreach ( $this->installables as $installable ) {
			$this->validate_instanceof( $installable, InstallableInterface::class );
		}
	}
}
