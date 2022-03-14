import * as React from 'react';

import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';

import { Divider } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useRequest, useSnackbar } from '@centreon/ui';

import { PasswordSecurityPolicy } from '../models';
import useValidationSchema from '../useValidationSchema';
import { putPasswordPasswordSecurityPolicy } from '../../api';
import {
  labelFailedToSavePasswordPasswordSecurityPolicy,
  labelPasswordPasswordSecurityPolicySaved,
} from '../translatedLabels';
import FormButtons from '../../FormButtons';

import PasswordCasePolicy from './PasswordCasePolicy';
import PasswordExpirationPolicy from './PasswordExpirationPolicy';
import PasswordBlockingPolicy from './PasswordBlockingPolicy';

interface Props {
  initialValues: PasswordSecurityPolicy;
  loadPasswordPasswordSecurityPolicy: () => void;
}

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 0, 1),
  },
  formGroup: {
    marginBottom: theme.spacing(1),
    marginTop: theme.spacing(1),
  },
}));

const Form = ({
  initialValues,
  loadPasswordPasswordSecurityPolicy,
}: Props): JSX.Element => {
  const classes = useStyles();
  const validationSchema = useValidationSchema();
  const { showSuccessMessage } = useSnackbar();
  const { t } = useTranslation();

  const { sendRequest } = useRequest({
    defaultFailureMessage: t(labelFailedToSavePasswordPasswordSecurityPolicy),
    request: putPasswordPasswordSecurityPolicy,
  });

  const submit = (
    values: PasswordSecurityPolicy,
    { setSubmitting },
  ): Promise<void> =>
    sendRequest(values)
      .then(() => {
        loadPasswordPasswordSecurityPolicy();
        showSuccessMessage(t(labelPasswordPasswordSecurityPolicySaved));
      })
      .finally(() => setSubmitting(false));

  return (
    <Formik<PasswordSecurityPolicy>
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
