import * as React from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';

import { Divider } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useRequest, useSnackbar } from '@centreon/ui';

import { SecurityPolicy } from '../models';
import useValidationSchema from '../useValidationSchema';
import { putSecurityPolicy } from '../api';
import {
  labelFailedToSavePasswordSecurityPolicy,
  labelPasswordSecurityPolicySaved,
} from '../translatedLabels';

import PasswordCasePolicy from './PasswordCasePolicy';
import PasswordExpirationPolicy from './PasswordExpirationPolicy';
import PasswordBlockingPolicy from './PasswordBlockingPolicy';
import FormButtons from './FormButtons';

interface Props {
  initialValues: SecurityPolicy;
  loadSecurityPolicy: () => void;
}

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 1, 1),
  },
  formGroup: {
    marginBottom: theme.spacing(1),
    marginTop: theme.spacing(1),
  },
}));

const Form = ({ initialValues, loadSecurityPolicy }: Props): JSX.Element => {
  const classes = useStyles();
  const validationSchema = useValidationSchema();
  const { showSuccessMessage } = useSnackbar();
  const { t } = useTranslation();

  const { sendRequest } = useRequest({
    defaultFailureMessage: t(labelFailedToSavePasswordSecurityPolicy),
    request: putSecurityPolicy,
  });

  const submit = (values: SecurityPolicy, { setSubmitting }): Promise<void> =>
    sendRequest(values)
      .then(() => {
        loadSecurityPolicy();
        showSuccessMessage(t(labelPasswordSecurityPolicySaved));
      })
      .finally(() => setSubmitting(false));

  return (
    <Formik<SecurityPolicy>
      enableReinitialize
      validateOnBlur
      validateOnMount
      initialValues={initialValues}
      validationSchema={validationSchema}
      onSubmit={submit}
    >
      {(): JSX.Element => (
        <div className={classes.formContainer}>
          <div className={classes.formGroup}>
            <PasswordCasePolicy />
          </div>
          <Divider />
          <div className={classes.formGroup}>
            <PasswordExpirationPolicy />
          </div>
          <Divider />
          <div className={classes.formGroup}>
            <PasswordBlockingPolicy />
          </div>
          <Divider />
          <div className={classes.formGroup}>
            <FormButtons />
          </div>
        </div>
      )}
    </Formik>
  );
};

export default Form;
