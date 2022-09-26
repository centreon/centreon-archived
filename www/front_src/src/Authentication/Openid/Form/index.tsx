import { FormikValues } from 'formik';
import { useTranslation } from 'react-i18next';
import { isEmpty, isNil, pick, pipe, values, or, all, not } from 'ramda';

import { useRequest, useSnackbar, Form } from '@centreon/ui';

import useValidationSchema from '../useValidationSchema';
import {
  labelFailedToSaveOpenidConfiguration,
  labelOpenIDConnectConfigurationSaved,
  labelRequired,
} from '../translatedLabels';
import { putProviderConfiguration } from '../../api';
import { OpenidConfiguration, OpenidConfigurationToAPI } from '../models';
import FormButtons from '../../FormButtons';
import { groups } from '../..';
import { Provider } from '../../models';
import { adaptOpenidConfigurationToAPI } from '../../api/adapters';

import { inputs } from './inputs';

interface Props {
  initialValues: OpenidConfiguration;
  isLoading: boolean;
  loadOpenidConfiguration: () => void;
}

const isNilOrEmpty = (value): boolean => or(isNil(value), isEmpty(value));

const OpenidForm = ({
  initialValues,
  loadOpenidConfiguration,
  isLoading,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const { sendRequest } = useRequest({
    defaultFailureMessage: t(labelFailedToSaveOpenidConfiguration),
    request: putProviderConfiguration<
      OpenidConfiguration,
      OpenidConfigurationToAPI
    >({ adapter: adaptOpenidConfigurationToAPI, type: Provider.Openid }),
  });
  const { showSuccessMessage } = useSnackbar();

  const validationSchema = useValidationSchema();

  const submit = (
    formikValues: OpenidConfiguration,
    { setSubmitting },
  ): Promise<void> =>
    sendRequest(formikValues)
      .then(() => {
        loadOpenidConfiguration();
        showSuccessMessage(t(labelOpenIDConnectConfigurationSaved));
      })
      .finally(() => setSubmitting(false));

  const validate = (formikValues: FormikValues): object => {
    const isUserInfoOrIntrospectionTokenEmpty = pipe(
      pick(['introspectionTokenEndpoint', 'userinfoEndpoint']),
      values,
      all(isNilOrEmpty),
    )(formikValues);

    if (not(isUserInfoOrIntrospectionTokenEmpty)) {
      return {};
    }

    return {
      introspectionTokenEndpoint: t(labelRequired),
      userinfoEndpoint: t(labelRequired),
    };
  };

  return (
    <Form<OpenidConfiguration>
      isCollapsible
      Buttons={FormButtons}
      groups={groups}
      initialValues={initialValues}
      inputs={inputs}
      isLoading={isLoading}
      submit={submit}
      validate={validate}
      validationSchema={validationSchema}
    />
  );
};

export default OpenidForm;
