/* eslint-disable react/jsx-one-expression-per-line */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import { Field, reduxForm as connectForm } from 'redux-form';
import { useTranslation } from 'react-i18next';

import { Button, Typography } from '@material-ui/core';

import styles from '../../styles/partials/form/_form.scss';
import RadioGroupFields from '../../components/form-fields/RadioGroupFields';
import {
  labelAddACentreonPoller,
  labelAddACentreonRemoteServer,
} from '../translatedLabels';

const configurationTypes = [
  {
    label: labelAddACentreonRemoteServer,
    value: 1,
  },
  {
    label: labelAddACentreonPoller,
    value: 2,
  },
];

const ServerConfigurationWizardForm = ({ error, handleSubmit, onSubmit }) => {
  const { t } = useTranslation();

  return (
    <div>
      <div className={styles['form-heading']}>
        <Typography variant="h6">{t('Choose a server type')}</Typography>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field
          component={RadioGroupFields}
          name="server_type"
          options={configurationTypes}
        />
        <div className={styles['form-buttons']}>
          <Button
            color="primary"
            size="small"
            type="submit"
            variant="contained"
          >
            {t('Next')}
          </Button>
        </div>
        {error ? (
          <Typography style={{ color: '#d0021b' }} variant="body2">
            {error}
          </Typography>
        ) : null}
      </form>
    </div>
  );
};

const validate = () => ({});

export default connectForm({ form: 'ServerConfigurationWizardForm', validate })(
  ServerConfigurationWizardForm,
);
