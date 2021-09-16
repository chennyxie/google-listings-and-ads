/**
 * External dependencies
 */
import { getHistory, getNewPath, getQuery } from '@woocommerce/navigation';
import {
	createInterpolateElement,
	useEffect,
	useCallback,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { recordEvent } from '@woocommerce/tracks';

/**
 * Internal dependencies
 */
import Guide from '.~/external-components/wordpress/guide';
import GuidePageContent, {
	ContentLink,
} from '.~/components/guide-page-content';
import AddPaidCampaignButton from '.~/components/paid-ads/add-paid-campaign-button';
import wooLogoURL from './woocommerce-logo.svg';
import googleLogoURL from './google-logo.svg';
import './index.scss';

const GUIDE_NAME = 'submission-success';
const EVENT_NAME = 'gla_modal_closed';
const LATER_BUTTON_CLASS = 'components-guide__finish-button';

const productFeedPath = getNewPath(
	{ guide: undefined },
	'/google/product-feed'
);

const image = (
	<div className="gla-submission-success-guide__logo-block">
		<div className="gla-submission-success-guide__logo-item">
			<img
				src={ wooLogoURL }
				alt={ __( 'WooCommerce Logo', 'google-listings-and-ads' ) }
				width="145"
				height="31"
			/>
		</div>
		<div className="gla-submission-success-guide__logo-separator-line" />
		<div className="gla-submission-success-guide__logo-item">
			<img
				src={ googleLogoURL }
				alt={ __( 'Google Logo', 'google-listings-and-ads' ) }
				width="106"
				height="36"
			/>
		</div>
	</div>
);

const pages = [
	{
		image,
		content: (
			<GuidePageContent
				title={ __(
					'You have successfully set up Google Listings & Ads! 🎉',
					'google-listings-and-ads'
				) }
			>
				<p>
					{ __(
						'Google reviews product listings in 3-5 days. If approved, your products will automatically be live and searchable on Google.',
						'google-listings-and-ads'
					) }
				</p>
				<p>
					{ createInterpolateElement(
						__(
							'<productFeedLink>Manage and edit your product feed in WooCommerce.</productFeedLink> We will also notify you of any product feed issues to ensure your products get approved and perform well on Google.',
							'google-listings-and-ads'
						),
						{
							productFeedLink: (
								<ContentLink
									href={ productFeedPath }
									context="product-feed"
								/>
							),
						}
					) }
				</p>
			</GuidePageContent>
		),
	},
	{
		image,
		content: (
			<GuidePageContent
				title={ __(
					'Boost listings with up to $150 free ad credits',
					'google-listings-and-ads'
				) }
			>
				<p>
					{ createInterpolateElement(
						__(
							'Give your products a boost and create a paid <link>Smart Shopping campaign</link>! Your ads will run once your products are approved by Google.',
							'google-listings-and-ads'
						),
						{
							link: (
								<ContentLink
									href="https://support.google.com/google-ads/answer/7674739"
									context="about-smart-shopping-campaigns"
								/>
							),
						}
					) }
				</p>
				<p>
					{ __(
						'Get up to $150* in free ad credit for if you’re new to Google Ads. You can edit or cancel your campaign at any time.',
						'google-listings-and-ads'
					) }
				</p>
				<cite>
					{ createInterpolateElement(
						__(
							'*Ad credit amounts vary by country and region. Eligibility criteria: The account has no other promotions applied. The account is billed to a country where Google Partners promotions are offered. The account served its first ad impression within the last 14 days. Review the static terms <link>here</link>.',
							'google-listings-and-ads'
						),
						{
							link: (
								<ContentLink
									href="https://www.google.com/ads/coupons/terms.html"
									context="terms-of-ads-coupons"
								/>
							),
						}
					) }
				</cite>
			</GuidePageContent>
		),
	},
];

const handleGuideFinish = ( e ) => {
	getHistory().replace( productFeedPath );

	// Since there is no built-in way to distinguish the modal/guide is closed by what action,
	// here is a workaround by identifying the close button's class name.
	const target = e.currentTarget || e.target;
	const action = target.classList.contains( LATER_BUTTON_CLASS )
		? 'maybe-later'
		: 'dismiss';
	recordEvent( EVENT_NAME, {
		context: GUIDE_NAME,
		action,
	} );
};

const GuideImplementation = () => {
	useEffect( () => {
		recordEvent( 'gla_modal_open', { context: GUIDE_NAME } );
	}, [] );

	const renderFinish = useCallback( () => {
		return (
			<>
				<div className="gla-submission-success-guide__space_holder" />
				<Button
					isSecondary
					className={ LATER_BUTTON_CLASS }
					onClick={ handleGuideFinish }
				>
					{ __( 'Maybe later', 'google-listings-and-ads' ) }
				</Button>
				<AddPaidCampaignButton
					isPrimary
					isSecondary={ false }
					isSmall={ false }
					eventName={ EVENT_NAME }
					eventProps={ {
						context: GUIDE_NAME,
						action: 'create-paid-campaign',
					} }
				>
					{ __( 'Create paid campaign', 'google-listings-and-ads' ) }
				</AddPaidCampaignButton>
			</>
		);
	}, [] );

	return (
		<Guide
			className="gla-submission-success-guide"
			backButtonText={ __( 'Back', 'google-listings-and-ads' ) }
			pages={ pages }
			renderFinish={ renderFinish }
			onFinish={ handleGuideFinish }
		/>
	);
};

/**
 * Modal window to greet the user at Product Feed, after successful completion of onboarding.
 *
 * Show this guide modal by visiting the path with a specific query `guide=submission-success`.
 * For example: `/wp-admin/admin.php?page=wc-admin&path=%2Fgoogle%2Fproduct-feed&guide=submission-success`.
 */
export default function SubmissionSuccessGuide() {
	const isOpen = getQuery().guide === GUIDE_NAME;

	if ( ! isOpen ) {
		return null;
	}
	return <GuideImplementation />;
}
