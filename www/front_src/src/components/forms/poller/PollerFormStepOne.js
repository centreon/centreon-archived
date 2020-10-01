/* eslint-disable camelcase */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import { withTranslation } from 'react-i18next';

import styles from '../../../styles/partials/form/_form.scss';
import InputField from '../../form-fields/InputField';
import RadioField from '../../form-fields/PreselectedRadioField';
import SelectField from '../../form-fields/SelectField';
import { validateFieldRequired } from '../../../helpers/validators';

class PollerFormStepOne extends Component {
  state = {
    inputTypeManual: true,
    initialized: false,
  };

  onManualInputChanged(inputTypeManual) {
    this.setState({
      inputTypeManual,
    });
  }

  initializeFromRest = (value) => {
    const { change } = this.props;
    change('inputTypeManual', !value);
    this.setState({
      initialized: true,
      inputTypeManual: !value,
    });
  };

  UNSAFE_componentWillReceiveProps = (nextProps) => {
    const { waitList } = nextProps;
    const { initialized } = this.state;
    if (waitList && !initialized) {
      this.initializeFromRest(waitList.length > 0);
    }
    this.setState({
      initialized: true,
    });
  };

  handleChange = (e, value) => {
    const { waitList, change } = this.props;
    const platform = waitList.find((server) => server.ip === value);
    change('server_name', platform.server_name);
  };

  render() {
    const { error, handleSubmit, onSubmit, waitList, t } = this.props;
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
              name="inputTypeManual"
              onChange={() => {
                this.onManualInputChanged(true);
              }}
              checked={inputTypeManual}
              component={RadioField}
              label={t('Create new Poller')}
            />
            {inputTypeManual ? (
              <div>
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
              </div>
            ) : null}
            <Field
              name="inputTypeManual"
              onClick={() => {
                this.onManualInputChanged(false);
              }}
              checked={!inputTypeManual}
              component={RadioField}
              label={`${t('Select a Poller')}:`}
            />
            {!inputTypeManual ? (
              <div>
                {waitList ? (
                  <Field
                    name="server_ip"
                    onChange={this.handleChange}
                    component={SelectField}
                    label={`${t('Select Pending Poller IP')}:`}
                    required
                    options={[
                      {
                        disabled: true,
                        selected: true,
                        text: t('Select IP'),
                        value: '',
                      },
                    ].concat(
                      waitList.map((c) => ({ value: c.ip, text: c.ip })),
                    )}
                  />
                ) : null}
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
              </div>
            ) : null}
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
