/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import { withTranslation } from 'react-i18next';

import styles from '../../../styles/partials/form/_form.scss';
import InputField from '../../form-fields/InputField';

import { validateFieldRequired } from '../../../helpers/validators';

class PollerFormStepOne extends Component {
  state = {
    inputTypeManual: true,
  }

  render() {
    const { error, handleSubmit, onSubmit, t } = this.props;
    const { inputTypeManual } = this.state;
    return (
      <div className={styles['form-wrapper']}>
        <div className={styles['form-inner']}>
          <div className={styles['form-heading']}>
            <h2 className={styles['form-title']}>
              {t('Server Configuration')}
            </h2>
          </div>
          <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
            <Field
              name="server_name"
              component={InputField}
              type="text"
              placeholder=""
              label={`${t('Server Name')}:`}
              validate={validateFieldRequired(t)}
            />
            <Field
              name="server_ip"
              component={InputField}
              type="text"
              placeholder=""
              label={`${t('Server IP address')}:`}
              validate={validateFieldRequired(t)}
            />
            <Field
              name="centreon_central_ip"
              component={InputField}
              type="text"
              placeholder=""
              label={`${t(
                'Centreon Central IP address, as seen by this server',
              )}:`}
              validate={validateFieldRequired(t)}
            />
            <Field
              name="inputTypeManual"
              onClick={() => {
                this.onManualInputChanged(false);
              }}
              checked={!inputTypeManual}
              component={RadioField}
              label={`${t('Select a Pending Poller')}:`}
            />
            <div className={styles['form-buttons']}>
              <button className={styles.button} type="submit">
                {t('Next')}
              </button>
            </div>
            {error ? (
              <div className={styles['error-block']}>{error.message}</div>
            ) : null}
          </form>
        </div>
      </div>
    );
  }
}

export default withTranslation()(
  connectForm({
    form: 'PollerFormStepOne',
    enableReinitialize: true,
    destroyOnUnmount: false,
    keepDirtyOnReinitialize: true,
  })(PollerFormStepOne),
);
