/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';
import classnames from 'classnames';
import { Field, reduxForm as connectForm } from 'redux-form';
import { Translate } from 'react-redux-i18n';
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

const ServerConfigurationWizardForm = ({ error, handleSubmit, onSubmit }) => (
  <div className={classnames(styles['form-wrapper'], styles.small)}>
    <div className={styles['form-inner']}>
      <div className={styles['form-heading']}>
        <h2 className={styles['form-title']}>
          <Translate value="Server Configuration Wizard" />
        </h2>
        <p className={styles['form-text']}>
          <Translate value="Choose a server type" />
          {':'}
        </p>
      </div>
      <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field
          name="server_type"
          component={RadioGroupFields}
          options={configurationTypes}
        />
        <div className={styles['form-buttons']}>
          <button className={styles.button} type="submit">
            <Translate value="Next" />
          </button>
        </div>
        {error ? (
          <div className={styles['error-block']}>{error.message}</div>
        ) : null}
      </form>
    </div>
  </div>
);

const validate = () => ({});

export default connectForm({ form: 'ServerConfigurationWizardForm', validate })(
  ServerConfigurationWizardForm,
);
