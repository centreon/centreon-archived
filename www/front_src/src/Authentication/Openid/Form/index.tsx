import * as React from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';

import { makeStyles } from '@mui/styles';

import { useRequest, useSnackbar } from '@centreon/ui';

import useValidationSchema from '../useValidationSchema';
import {
  labelFailedToSaveOpenidConfiguration,
  labelOpenIDConnectConfigurationSaved,
} from '../translatedLabels';
import { putOpenidConfiguration } from '../../api';
import { OpenidConfiguration } from '../models';
import FormButtons from '../../FormButtons';
import Inputs from '../../FormInputs';
import { categories } from '../..';

import { inputs } from './inputs';

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 0, 0),
  },
}));

interface Props {
  initialValues: OpenidConfiguration;
  loadOpenidConfiguration: () => void;
}

const Form = ({
  initialValues,
  loadOpenidConfiguration,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { sendRequest } = useRequest({
    defaultFailureMessage: t(labelFailedToSaveOpenidConfiguration),
    request: putOpenidConfiguration,
  });
  const { showSuccessMessage } = useSnackbar();

  const validationSchema = useValidationSchema();

  const submit = (
    values: OpenidConfiguration,
    { setSubmitting },
  ): Promise<void> =>
    sendRequest(values)
      .then(() => {
        loadOpenidConfiguration();
        showSuccessMessage(t(labelOpenIDConnectConfigurationSaved));
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
