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
    inputTypeManual: true,
    initialized: false,
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
      centreon_folder: '/centreon/',
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
            <h2 className={classnames(styles['form-title'], styles['mb-2'])}>
              {t('Remote Server Configuration')}
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
              label={t('Create new Remote Server')}
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
                  name="db_user"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={`${t('Database username')}:`}
                  validate={validateFieldRequired(t)}
                />
                <Field
                  name="db_password"
                  component={InputField}
                  type="password"
                  placeholder=""
                  label={`${t('Database password')}:`}
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
                  name="centreon_folder"
                  component={InputField}
                  type="text"
                  placeholder="/centreon/"
                  label={`${t('Centreon Web Folder on Remote')}:`}
                  validate={validateFieldRequired(t)}
                />
                <Field
                  name="no_check_certificate"
                  component={CheckboxField}
                  label={t('Do not check SSL certificate validation')}
                />
                <Field
                  name="no_proxy"
                  component={CheckboxField}
                  label={t(
                    'Do not use configured proxy to connect to this server',
                  )}
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
              label={`${t('Select a Remote Server')}:`}
            />
            {!inputTypeManual ? (
              <div>
                {waitList ? (
                  <Field
                    name="server_ip"
                    onChange={this.handleChange}
                    component={SelectField}
                    label={`${t('Select Pending Remote Links')}:`}
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
                  name="db_user"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={`${t('Database username')}:`}
                  validate={validateFieldRequired(t)}
                />
                <Field
                  name="db_password"
                  component={InputField}
                  type="password"
                  placeholder=""
                  label={`${t('Database password')}:`}
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
                  name="centreon_folder"
                  component={InputField}
                  type="text"
                  placeholder="/centreon/"
                  label={`${t('Centreon Web Folder on Remote')}:`}
                  validate={validateFieldRequired(t)}
                />
                <Field
                  name="no_check_certificate"
                  component={CheckboxField}
                  label={t('Do not check SSL certificate validation')}
                />
                <Field
                  name="no_proxy"
                  component={CheckboxField}
                  label={t(
                    'Do not use configured proxy to connect to this server',
                  )}
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
    form: 'RemoteServerFormStepOne',
    enableReinitialize: true,
    destroyOnUnmount: false,
    keepDirtyOnReinitialize: true,
  })(RemoteServerFormStepOne),
);
