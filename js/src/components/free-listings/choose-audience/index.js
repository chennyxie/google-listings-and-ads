/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Form } from '@woocommerce/components';

/**
 * Internal dependencies
 */
import AppSpinner from '.~/components/app-spinner';
import useTargetAudience from '.~/hooks/useTargetAudience';
import StepContent from '.~/components/stepper/step-content';
import StepContentHeader from '.~/components/stepper/step-content-header';
import FormContent from './form-content';
import '.~/components/free-listings/choose-audience/index.scss';

/**
 * Step with a form to choose audience.
 *
 * To be used in onboarding and further editing.
 * Does not provide any save strategy, this is to be bound externaly.
 * Copied from {@link .~/setup-mc/setup-stepper/choose-audience/index.js}.
 *
 * @param {Object} props
 * @param {string} props.stepHeader Header text to indicate the step number.
 * @param {function(Object)} props.onContinue Callback called with form data once continue button is clicked.
 */
export default function ChooseAudience( {
	stepHeader,
	onContinue = () => {},
} ) {
	const { data } = useTargetAudience();

	if ( ! data ) {
		return <AppSpinner />;
	}

	const handleValidate = () => {
		const errors = {};

		// TODO: validation logic.

		return errors;
	};

	const handleSubmitCallback = () => {
		onContinue();
	};

	return (
		<div className="gla-choose-audience">
			<StepContent>
				<StepContentHeader
					step={ stepHeader }
					title={ __(
						'Choose your audience',
						'google-listings-and-ads'
					) }
					description={ __(
						'Configure who sees your product listings on Google.',
						'google-listings-and-ads'
					) }
				/>
				{ data && (
					<Form
						initialValues={ {
							locale: data.locale,
							language: data.language,
							location: data.location,
							countries: data.countries || [],
						} }
						validate={ handleValidate }
						onSubmitCallback={ handleSubmitCallback }
					>
						{ ( formProps ) => {
							return <FormContent formProps={ formProps } />;
						} }
					</Form>
				) }
			</StepContent>
		</div>
	);
}