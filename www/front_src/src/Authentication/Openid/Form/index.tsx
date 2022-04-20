import * as React from 'react';

import { Formik, FormikErrors, FormikValues } from 'formik';
import { useTranslation } from 'react-i18next';
import { isEmpty, isNil, pick, pipe, values, or, all, not } from 'ramda';

import { makeStyles } from '@mui/styles';

import { useRequest, useSnackbar } from '@centreon/ui';

import useValidationSchema from '../useValidationSchema';
import {
  labelFailedToSaveOpenidConfiguration,
  labelIntrospectionTokenEndpoint,
  labelNeedTobeFilled,
  labelOpenIDConnectConfigurationSaved,
  labelOr,
  labelRequired,
  labelUserInformationEndpoint,
} from '../translatedLabels';
import { putProviderConfiguration } from '../../api';
import { OpenidConfiguration, OpenidConfigurationToAPI } from '../models';
import FormButtons from '../../FormButtons';
import Inputs from '../../FormInputs';
import { categories } from '../..';
import { Provider } from '../../models';
import { adaptOpenidConfigurationToAPI } from '../../api/adapters';

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

const isNilOrEmpty = (value): boolean => or(isNil(value), isEmpty(value));

const Form = ({
  initialValues,
  loadOpenidConfiguration,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { sendRequest } = useRequest({
    defaultFailureMessage: t(labelFailedToSaveOpenidConfiguration),
    request: putProviderConfiguration<
      OpenidConfiguration,
      OpenidConfigurationToAPI
    >({ adapter: adaptOpenidConfigurationToAPI, type: Provider.Openid }),
  });
  const { showSuccessMessage, showErrorMessage } = useSnackbar();

  const validationSchema = useValidationSchema();

  const submit = (
    formikValues: OpenidConfiguration,
    { setSubmitting },
  ): void => {
    const areUserInfoOrIntrospectionTokenFilled = pipe(
      pick(['introspectionTokenEndpoint', 'userinfoEndpoint']),
      values,
      all(isNilOrEmpty),
    )(formikValues);

    if (areUserInfoOrIntrospectionTokenFilled) {
      showErrorMessage(
        `"${t(labelIntrospectionTokenEndpoint)}" ${t(
          labelOr,
        )} "${labelUserInformationEndpoint}" ${t(labelNeedTobeFilled)}`,
      );
      setSubmitting(false);

      return;
    }
    sendRequest(formikValues)
      .then(() => {
        loadOpenidConfiguration();
        showSuccessMessage(t(labelOpenIDConnectConfigurationSaved));
      })
      .finally(() => setSubmitting(false));
  };

  return (
    <Formik
      enableReinitialize
      validateOnBlur
      validateOnMount
      initialValues={initialValues}
      validate={(formikValues): object => {
        const baseErrors = validationSchema.validate(values);

        const isUserInfoOrIntrospectionTokenEmpty = pipe(
          pick(['introspectionTokenEndpoint', 'userinfoEndpoint']),
          values,
          all(isNilOrEmpty),
        )(formikValues);

        if (not(isUserInfoOrIntrospectionTokenEmpty)) {
          return baseErrors;
        }

        return {
          ...baseErrors,
          introspectionTokenEndpoint: t(labelRequired),
          userinfoEndpoint: t(labelRequired),
        };
      }}
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
