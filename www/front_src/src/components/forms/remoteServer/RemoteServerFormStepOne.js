/* eslint-disable camelcase */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/no-unused-state */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';

import { Field, reduxForm as connectForm } from 'redux-form';
import classnames from 'classnames';
import { withTranslation } from 'react-i18next';

import styles from '../../../styles/partials/form/_form.scss';
import InputField from '../../form-fields/InputField';
import SelectField from '../../form-fields/SelectField';
import RadioField from '../../form-fields/PreselectedRadioField';
import CheckboxField from '../../form-fields/CheckboxField';
import { validateFieldRequired } from '../../../helpers/validators';

class RemoteServerFormStepOne extends Component {
  state = {
    initialized: false,
    inputTypeManual: true,
  };

  onManualInputChanged(inputTypeManual) {
    this.setState({
      inputTypeManual,
    });
  }

  initializeFromRest = (value) => {
    this.props.change('inputTypeManual', !value);
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
    const { error, handleSubmit, onSubmit, waitList, defaultCentralIp, t } = this.props;
    const { inputTypeManual } = this.state;
    return (
      <div className={styles['form-wrapper']}>
        <div className={styles['form-inner']}>
          <div className={styles['form-heading']}>
            <h2 className={classnames(styles['form-title'], styles['mb-2'])}>
              {t('Remote Server Configuration')}
            </h2>
          </div>
          <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
            <Field
              checked={inputTypeManual}
              component={RadioField}
              label={t('Create new Remote Server')}
              name="inputTypeManual"
              onChange={() => {
                this.onManualInputChanged(true);
              }}
            />
            {inputTypeManual ? (
              <div>
                <Field
                  component={InputField}
                  label={`${t('Server Name')}:`}
                  name="server_name"
                  placeholder=""
                  type="text"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t('Server IP address')}:`}
                  name="server_ip"
                  placeholder=""
                  type="text"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t('Database username')}:`}
                  name="db_user"
                  placeholder=""
                  type="text"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t('Database password')}:`}
                  name="db_password"
                  placeholder=""
                  type="password"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t(
                    'Centreon Central IP address, as seen by this server',
                  )}:`}
                  name="centreon_central_ip"
                  placeholder=""
                  type="text"
                  validate={validateFieldRequired(t)}
                />
              </div>
            ) : null}

            <Field
              checked={!inputTypeManual}
              component={RadioField}
              label={`${t('Select a Remote Server')}:`}
              name="inputTypeManual"
              onClick={() => {
                this.onManualInputChanged(false);
              }}
            />
            {!inputTypeManual ? (
              <div>
                {waitList ? (
                  <Field
                    required
                    component={SelectField}
                    label={`${t('Select Pending Remote Links')}:`}
                    name="server_ip"
                    options={[
                      {
                        disabled: true,
                        selected: true,
                        text: t('Select IP'),
                        value: '',
                      },
                    ].concat(
                      waitList.map((c) => ({ text: c.ip, value: c.ip })),
                    )}
                    onChange={this.handleChange}
                  />
                ) : null}
                <Field
                  component={InputField}
                  label={`${t('Server Name')}:`}
                  name="server_name"
                  placeholder=""
                  type="text"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t('Database username')}:`}
                  name="db_user"
                  placeholder=""
                  type="text"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t('Database password')}:`}
                  name="db_password"
                  placeholder=""
                  type="password"
                  validate={validateFieldRequired(t)}
                />
                <Field
                  component={InputField}
                  label={`${t(
                    'Centreon Central IP address, as seen by this server',
                  )}:`}
                  name="centreon_central_ip"
                  placeholder=""
                  type="text"
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
    destroyOnUnmount: false,
    enableReinitialize: true,
    form: 'RemoteServerFormStepOne',
    keepDirtyOnReinitialize: true,
  })(RemoteServerFormStepOne),
);
