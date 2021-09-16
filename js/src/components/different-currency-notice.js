/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useGoogleAdsAccount from '.~/hooks/useGoogleAdsAccount';
import useStoreCurrency from '.~/hooks/useStoreCurrency';
import AppDocumentationLink from '.~/components/app-documentation-link';

/**
 * Shows warning {@link Notice}
 * when the store's currency is different than the one set in Google Ads account.
 *
 * @param {Object} props React props.
 * @param {string} props.context Context or page on which the notice is shown, to be forwarded to the link's track event.
 * @return {JSX.Element} {@link Notice} element with the warning message and the link to the documentation.
 */
export default function DifferentCurrencyNotice( { context } ) {
	const { googleAdsAccount } = useGoogleAdsAccount();
	const { code: storeCurrency } = useStoreCurrency();

	// Do not render if data is not available, or the same currencies are used.
	if ( ! googleAdsAccount || googleAdsAccount.currency === storeCurrency ) {
		return null;
	}

	return (
		<Notice
			className="gla-different-currency-notice"
			status="warning"
			isDismissible={ false }
		>
			{ createInterpolateElement(
				__(
					'Note: The currency set in your Google Ads account is <adsCurrency />, which is different from your store currency, <storeCurrency />. <readMoreLink>Read more</readMoreLink>',
					'google-listings-and-ads'
				),
				{
					adsCurrency: <strong>{ googleAdsAccount.currency }</strong>,
					storeCurrency: <strong>{ storeCurrency }</strong>,
					// `ExternalIcon` is not addd here as that should be done uniformly across all `AppDocumentationLink`s:
					// https://github.com/woocommerce/google-listings-and-ads/issues/984
					readMoreLink: (
						<AppDocumentationLink
							className="gla-different-currency-notice__link"
							href="https://support.google.com/google-ads/answer/9841530"
							eventName="gla_different_currency_notice_link_click"
							context={ context }
							linkId="setting-up-currency"
						/>
					),
				}
			) }
		</Notice>
	);
}
