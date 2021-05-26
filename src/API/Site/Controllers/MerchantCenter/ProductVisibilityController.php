<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\MerchantCenter;

use Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\BaseController;
use Automattic\WooCommerce\GoogleListingsAndAds\API\TransportMethods;
use Automattic\WooCommerce\GoogleListingsAndAds\DB\Query\MerchantIssueQuery;
use Automattic\WooCommerce\GoogleListingsAndAds\Internal\ContainerAwareTrait;
use Automattic\WooCommerce\GoogleListingsAndAds\Internal\Interfaces\ContainerAwareInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\PluginHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\ProductHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\ProductMetaHandler;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\RESTServer;
use Automattic\WooCommerce\GoogleListingsAndAds\Value\ChannelVisibility;
use Exception;
use WP_REST_Request as Request;
use WP_REST_Response as Response;

defined( 'ABSPATH' ) || exit;

/**
 * Class ProductVisibilityController
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\MerchantCenter
 */
class ProductVisibilityController extends BaseController implements ContainerAwareInterface {

	use PluginHelper;
	use ContainerAwareTrait;

	/**
	 * @var ProductHelper $product_helper
	 */
	protected $product_helper;
	/**
	 * @var ProductMetaHandler $meta_handler
	 */
	protected $meta_handler;

	/**
	 * ProductVisibilityController constructor.
	 *
	 * @param RESTServer         $server
	 * @param ProductHelper      $product_helper
	 * @param ProductMetaHandler $meta_handler
	 */
	public function __construct( RESTServer $server, ProductHelper $product_helper, ProductMetaHandler $meta_handler ) {
		parent::__construct( $server );
		$this->product_helper = $product_helper;
		$this->meta_handler   = $meta_handler;
	}

	/**
	 * Register rest routes with WordPress.
	 */
	public function register_routes(): void {
		$this->register_route(
			'mc/product-visibility',
			[
				[
					'methods'             => TransportMethods::EDITABLE,
					'callback'            => $this->get_update_callback(),
					'permission_callback' => $this->get_permission_callback(),
					'args'                => $this->get_collection_params(),
				],
				'schema' => $this->get_api_response_schema_callback(),
			]
		);
	}

	/**
	 * Get a callback for updating products' channel visibility.
	 *
	 * @return callable
	 */
	protected function get_update_callback(): callable {
		return function( Request $request ) {
			$ids     = $request->get_param( 'ids' );
			$visible = $request->get_param( 'visible' );

			$success = [];
			$errors  = [];
			foreach ( $ids as $product_id ) {
				$product_id = intval( $product_id );
				if ( ! $this->change_product_visibility( $product_id, $visible ) ) {
					$errors[] = $product_id;
					continue;
				}

				if ( ! $visible ) {
					$this->remove_product_issues( $product_id );
				}
				$success[] = $product_id;
			}

			sort( $success );
			sort( $errors );
			return new Response(
				[
					'success' => $success,
					'errors'  => $errors,
				],
				count( $errors ) ? 400 : 200
			);
		};
	}

	/**
	 * Get the item schema for the controller.
	 *
	 * @return array
	 */
	protected function get_schema_properties(): array {
		return [
			'success' => [
				'type'              => 'array',
				'description'       => __( 'Products whose visibility was changed successfully.', 'google-listings-and-ads' ),
				'context'           => [ 'view' ],
				'validate_callback' => 'rest_validate_request_arg',
				'items'             => [
					'type' => 'numeric',
				],
			],
			'errors'  => [
				'type'              => 'array',
				'description'       => __( 'Products whose visibility was not changed.', 'google-listings-and-ads' ),
				'context'           => [ 'view' ],
				'validate_callback' => 'rest_validate_request_arg',
				'items'             => [
					'type' => 'numeric',
				],
			],
		];
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		return [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
			'ids'     => [
				'description'       => __( 'IDs of the products to update.', 'google-listings-and-ads' ),
				'type'              => 'array',
				'sanitize_callback' => 'wp_parse_slug_list',
				'validate_callback' => 'rest_validate_request_arg',
				'items'             => [
					'type' => 'integer',
				],
			],
			'visible' => [
				'description'       => __( 'New Visibility status for the specified products.', 'google-listings-and-ads' ),
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			],
		];
	}

	/**
	 * Get the item schema name for the controller.
	 *
	 * Used for building the API response schema.
	 *
	 * @return string
	 */
	protected function get_schema_title(): string {
		return 'product_visibility';
	}

	/**
	 * Update a product's Merchant Center visibility setting (or parent product, for variations).
	 *
	 * @param int  $product_id
	 * @param bool $new_visibility True for visible, false for not visible.
	 *
	 * @return bool True if the product was found and updated correctly.
	 */
	protected function change_product_visibility( int $product_id, bool $new_visibility ): bool {
		try {
			$product_id = $this->product_helper->maybe_swap_for_parent_id( $product_id );
			// Use $product->save() instead of ProductMetaHandler to trigger MC sync.
			$product = wc_get_product( $product_id );
			$product->update_meta_data(
				$this->prefix_meta_key( ProductMetaHandler::KEY_VISIBILITY ),
				$new_visibility ? ChannelVisibility::SYNC_AND_SHOW : ChannelVisibility::DONT_SYNC_AND_SHOW
			);
			$product->save();
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Delete cached Merchant Center issues associated with the given product ID.
	 *
	 * @param int $product_id
	 */
	protected function remove_product_issues( int $product_id ): void {
		/** @var MerchantIssueQuery $issue_query */
		$issue_query = $this->container->get( MerchantIssueQuery::class );
		$issue_query->delete( 'product_id', $product_id );
	}
}
