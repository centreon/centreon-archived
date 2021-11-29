import * as React from 'react';

import { Formik } from 'formik';

import { makeStyles } from '@material-ui/core';

import { SecurityPolicy } from '../models';
import useValidationSchema from '../validationSchema';

import PasswordCasePolicy from './PasswordCasePolicy';

interface Props {
  initialValues: SecurityPolicy;
}

const useStyles = makeStyles((theme) => ({
  formContainer: {
    margin: theme.spacing(2, 1),
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
          <PasswordCasePolicy />
        </div>
      )}
    </Formik>
  );
};

export default Form;
