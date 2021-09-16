/**
 * External dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useJetpackAccount from '.~/hooks/useJetpackAccount';
import useGoogleAccount from '.~/hooks/useGoogleAccount';
import useGoogleMCAccount from '.~/hooks/useGoogleMCAccount';
import AppSpinner from '.~/components/app-spinner';
import StepContent from '.~/components/stepper/step-content';
import StepContentHeader from '.~/components/stepper/step-content-header';
import StepContentFooter from '.~/components/stepper/step-content-footer';
import WordPressDotComAccount from './wordpressdotcom-account';
import GoogleAccount from '.~/components/google-account';
import GoogleMCAccount from './google-mc-account';
import Faqs from './faqs';

const SetupAccounts = ( props ) => {
	const { onContinue = () => {} } = props;
	const { jetpack } = useJetpackAccount();
	const { google } = useGoogleAccount();
	const { googleMCAccount } = useGoogleMCAccount();

	if (
		! jetpack ||
		( jetpack.active === 'yes' &&
			( ! google || ( google.active === 'yes' && ! googleMCAccount ) ) )
	) {
		return <AppSpinner />;
	}

	const isGoogleAccountDisabled = jetpack?.active !== 'yes';
	const isGoogleMCAccountDisabled = google?.active !== 'yes';
	const isContinueButtonDisabled = googleMCAccount?.status !== 'connected';

	return (
		<StepContent>
			<StepContentHeader
				title={ __(
					'Set up your accounts',
					'google-listings-and-ads'
				) }
				description={ __(
					'Connect your Wordpress.com account, Google account, and Google Merchant Center account to use Google Listings & Ads.',
					'google-listings-and-ads'
				) }
			/>
			<WordPressDotComAccount />
			<GoogleAccount disabled={ isGoogleAccountDisabled } />
			<GoogleMCAccount disabled={ isGoogleMCAccountDisabled } />
			<Faqs />
			<StepContentFooter>
				<Button
					isPrimary
					disabled={ isContinueButtonDisabled }
					onClick={ onContinue }
				>
					{ __( 'Continue', 'google-listings-and-ads' ) }
				</Button>
			</StepContentFooter>
		</StepContent>
	);
};

export default SetupAccounts;
