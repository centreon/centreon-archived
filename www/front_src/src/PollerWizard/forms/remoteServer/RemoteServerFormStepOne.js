/* eslint-disable camelcase */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/no-unused-state */
/* eslint-disable react/destructuring-assignment */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';

import { Field, reduxForm as connectForm } from 'redux-form';
import { withTranslation } from 'react-i18next';

import { Typography, Button } from '@material-ui/core';

import styles from '../../../styles/partials/form/_form.scss';
import InputField from '../../../components/form-fields/InputField';
import SelectField from '../../../components/form-fields/SelectField';
import RadioField from '../../../components/form-fields/PreselectedRadioField';
import CheckboxField from '../../../components/form-fields/CheckboxField';
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
      centreon_folder: '/centreon/',
      initialized: true,
    });
  };

  handleChange = (e, value) => {
    const { waitList, change } = this.props;
    const platform = waitList.find((server) => server.ip === value);
    change('server_name', platform.server_name);
  };

  render() {
    const { error, handleSubmit, onSubmit, waitList, t, goToPreviousStep } =
      this.props;
    const { inputTypeManual } = this.state;

    return (
      <div>
        <div className={styles['form-heading']}>
          <Typography variant="h6">
            {t('Remote Server Configuration')}
          </Typography>
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
                label={`${t('Server Name')}`}
                name="server_name"
                placeholder=""
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t('Server IP address')}`}
                name="server_ip"
                placeholder=""
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t('Database username')}`}
                name="db_user"
                placeholder=""
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t('Database password')}`}
                name="db_password"
                placeholder=""
                type="password"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t(
                  'Centreon Central IP address, as seen by this server',
                )}`}
                name="centreon_central_ip"
                placeholder=""
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t('Centreon Web Folder on Remote')}`}
                name="centreon_folder"
                placeholder="/centreon/"
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={CheckboxField}
                label={t('Do not check SSL certificate validation')}
                name="no_check_certificate"
              />
              <Field
                component={CheckboxField}
                label={t(
                  'Do not use configured proxy to connect to this server',
                )}
                name="no_proxy"
              />
            </div>
          ) : null}

          <Field
            checked={!inputTypeManual}
            component={RadioField}
            label={`${t('Select a Remote Server')}`}
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
                  label={`${t('Select Pending Remote Links')}`}
                  name="server_ip"
                  options={[
                    {
                      disabled: true,
                      selected: true,
                      text: t('Select IP'),
                      value: '',
                    },
                  ].concat(waitList.map((c) => ({ text: c.ip, value: c.ip })))}
                  onChange={this.handleChange}
                />
              ) : null}
              <Field
                component={InputField}
                label={`${t('Server Name')}`}
                name="server_name"
                placeholder=""
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t('Database username')}`}
                name="db_user"
                placeholder=""
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={InputField}
                label={`${t('Database password')}`}
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
              <Field
                component={InputField}
                label={`${t('Centreon Web Folder on Remote')}`}
                name="centreon_folder"
                placeholder="/centreon/"
                type="text"
                validate={validateFieldRequired(t)}
              />
              <Field
                component={CheckboxField}
                label={t('Do not check SSL certificate validation')}
                name="no_check_certificate"
              />
              <Field
                component={CheckboxField}
                label={t(
                  'Do not use configured proxy to connect to this server',
                )}
                name="no_proxy"
              />
            </div>
          ) : null}

          <div className={styles['form-buttons']}>
            <Button size="small" onClick={goToPreviousStep}>
              {t('Previous')}
            </Button>
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
            <Typography color="error" variant="body2">
              {error.message}
            </Typography>
          ) : null}
        </form>
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
