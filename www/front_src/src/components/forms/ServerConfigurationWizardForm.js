/* eslint-disable react/jsx-one-expression-per-line */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import classnames from 'classnames';
import { Field, reduxForm as connectForm } from 'redux-form';
import { useTranslation } from 'react-i18next';

import { Button, Typography } from '@material-ui/core';

import styles from '../../styles/partials/form/_form.scss';
import RadioGroupFields from '../form-fields/RadioGroupFields';

const configurationTypes = [
  {
    label: 'Add a Centreon Remote Server',
    value: 1,
  },
  {
    label: 'Add a Centreon Poller',
    value: 2,
  },
];

const ServerConfigurationWizardForm = ({ error, handleSubmit, onSubmit }) => {
  const { t } = useTranslation();

  return (
    <div className={classnames(styles['form-wrapper'], styles.small)}>
      <div className={styles['form-inner']}>
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
            <Button color="primary" size="small" type="submit">
              {t('Next')}
            </Button>
          </div>
          {error ? (
            <div className={styles['error-block']}>{error.message}</div>
          ) : null}
        </form>
      </div>
    </div>
  );
};

const validate = () => ({});

export default connectForm({ form: 'ServerConfigurationWizardForm', validate })(
  ServerConfigurationWizardForm,
);
