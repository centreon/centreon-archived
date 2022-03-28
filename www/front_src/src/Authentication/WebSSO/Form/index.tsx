import * as React from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';

import { useRequest, useSnackbar } from '@centreon/ui';

import useValidationSchema from '../useValidationSchema';
import {
  labelFailedToSaveWebSSOConfiguration,
  labelWebSSOConfigurationSaved,
} from '../translatedLabels';
import { putProviderConfiguration } from '../../api';
import { WebSSOConfiguration, WebSSOConfigurationToAPI } from '../models';
import FormButtons from '../../FormButtons';
import Inputs from '../../FormInputs';
import { categories } from '../..';
import { Provider } from '../../models';
import { adaptWebSSOConfigurationToAPI } from '../../api/adapters';

import { inputs } from './inputs';

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 0, 0),
  },
}));

interface Props {
  initialValues: WebSSOConfiguration;
  loadWebSSOonfiguration: () => void;
}

const Form = ({
  initialValues,
  loadWebSSOonfiguration,
}: Props): JSX.Element => {
  const classes = useStyles();
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
    <Formik
      enableReinitialize
      validateOnBlur
      validateOnMount
      initialValues={initialValues}
      validationSchema={validationSchema}
      onSubmit={submit}
    >
      <div className={classes.formContainer}>
        <Inputs categories={categories} inputs={inputs} />
        <FormButtons />
      </div>
    </Formik>
  );
};

export default Form;
