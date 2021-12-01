import * as React from 'react';

import { Formik } from 'formik';

import { Divider, makeStyles } from '@material-ui/core';

import { SecurityPolicy } from '../models';
import useValidationSchema from '../useValidationSchema';

import PasswordCasePolicy from './PasswordCasePolicy';
import PasswordExpirationPolicy from './PasswordExpirationPolicy';
import PasswordBlockingPolicy from './PasswordBlockingPolicy';

interface Props {
  initialValues: SecurityPolicy;
}

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 1),
  },
  formGroup: {
    marginBottom: theme.spacing(1),
  },
}));

const Form = ({ initialValues }: Props): JSX.Element => {
  const classes = useStyles();
  const validationSchema = useValidationSchema();

  const submit = (values: SecurityPolicy, { setSubmitting }): void => {
    console.log(values);
    setSubmitting(false);
  };

  return (
    <Formik<SecurityPolicy>
      validateOnBlur
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
        </div>
      )}
    </Formik>
  );
};

export default Form;
