/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { getPath } from '@woocommerce/navigation';

/**
 * Internal dependencies
 */
import AppTabNav from '.~/components/app-tab-nav';
import isWCNavigationEnabled from '.~/utils/isWCNavigationEnabled';
import AppSubNav from '.~/components/app-sub-nav';

const tabs = [
	{
		key: 'programs',
		title: __( 'Programs', 'google-listings-and-ads' ),
		path: '/google/reports/programs',
	},
	{
		key: 'products',
		title: __( 'Products', 'google-listings-and-ads' ),
		path: '/google/reports/products',
	},
];

const ReportsNavigation = () => {
	const navigationEnabled = isWCNavigationEnabled();
	const path = getPath();
	const selectedKey = tabs.find( ( el ) => el.path === path )?.key;

	return navigationEnabled ? (
		<AppTabNav tabs={ tabs } selectedKey={ selectedKey } />
	) : (
		<AppSubNav tabs={ tabs } selectedKey={ selectedKey } />
	);
};

export default ReportsNavigation;