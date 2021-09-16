<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Tests\Tools\HelperTrait;

use Automattic\WooCommerce\GoogleListingsAndAds\Exception\MerchantApiException;
use Google\Service\ShoppingContent\Account;
use Google\Service\ShoppingContent\AccountAddress;
use Google\Service\ShoppingContent\AccountBusinessInformation;

/**
 * Trait MerchantTrait
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Tests\Tools\HelperTrait
 */
trait MerchantTrait {

	protected $valid_account_phone_number = '+18008675309';
	protected $valid_account_id = '123581321';

	public function get_account_exception( int $code = 400 ): MerchantApiException {
		return MerchantApiException::account_retrieve_failed( $code );
	}

	public function get_empty_account(): Account {
		return new Account();
	}

	public function get_sample_address(): AccountAddress {
		$address = new AccountAddress();
		$address->setCountry( 'US' );
		$address->setRegion( 'California' );
		$address->setLocality( 'San Fransisco' );
		$address->setStreetAddress( '123 Main St.' );
		$address->setPostalCode( '12345' );

		return $address;
	}

	public function get_valid_account(): Account {
		$account       = new Account();
		$business_info = new AccountBusinessInformation();
		$business_info->setPhoneNumber( $this->valid_account_phone_number );
		$business_info->setAddress( $this->get_sample_address() );
		$account->setBusinessInformation( $business_info );

		return $account;
	}
}
