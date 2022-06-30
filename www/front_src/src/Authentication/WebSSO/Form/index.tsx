import { useTranslation } from 'react-i18next';

import { useRequest, useSnackbar, Form } from '@centreon/ui';

import useValidationSchema from '../useValidationSchema';
import {
  labelFailedToSaveWebSSOConfiguration,
  labelWebSSOConfigurationSaved,
} from '../translatedLabels';
import { putProviderConfiguration } from '../../api';
import { WebSSOConfiguration, WebSSOConfigurationToAPI } from '../models';
import { categories } from '../..';
import { Provider } from '../../models';
import { adaptWebSSOConfigurationToAPI } from '../../api/adapters';
import FormButtons from '../../FormButtons';

import { inputs } from './inputs';

interface Props {
  initialValues: WebSSOConfiguration;
  isLoading: boolean;
  loadWebSSOonfiguration: () => void;
}

const WebSSOForm = ({
  initialValues,
  loadWebSSOonfiguration,
  isLoading,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const { sendRequest } = useRequest({
    defaultFailureMessage: t(labelFailedToSaveWebSSOConfiguration),
    request: putProviderConfiguration<
      WebSSOConfiguration,
      WebSSOConfigurationToAPI
    >({
      adapter: adaptWebSSOConfigurationToAPI,
      type: Provider.WebSSO,
    }),
  });
  const { showSuccessMessage } = useSnackbar();

  const validationSchema = useValidationSchema();

  const submit = (
    values: WebSSOConfiguration,
    { setSubmitting },
  ): Promise<void> =>
    sendRequest(values)
      .then(() => {
        loadWebSSOonfiguration();
        showSuccessMessage(t(labelWebSSOConfigurationSaved));
      })
      .finally(() => setSubmitting(false));

  return (
    <Form<WebSSOConfiguration>
      Buttons={FormButtons}
      groups={categories}
      initialValues={initialValues}
      inputs={inputs}
      isLoading={isLoading}
      submit={submit}
      validationSchema={validationSchema}
    />
  );

  // return (
  //   <Formik
  //     enableReinitialize
  //     validateOnBlur
  //     validateOnMount
  //     initialValues={initialValues}
  //     validationSchema={validationSchema}
  //     onSubmit={submit}
  //   >
  //     <div className={classes.formContainer}>
  //       <Inputs categories={categories} inputs={inputs} />
  //       <FormButtons />
  //     </div>
  //   </Formik>
  // );
};

export default WebSSOForm;
