import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import {Link} from 'react-router-dom';
import InputField from '../../form-fields/InputField';
import CheckboxField from '../../form-fields/CheckboxField';
import routeMap from '../../../route-maps';

import { 
  serverNameValidator, 
  serverIpAddressValidator, 
  centraIpAddressValidator,
  databaseUserValidator,
  databasePasswordValidator
} from '../../../helpers/validators';

class PollerFormStepOne extends Component {

  render() {
    const {error, handleSubmit, onSubmit, submitting} = this.props;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title">Server Configuration</h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
            <Field
              name="serverName"
              component={InputField}
              type="text"
              placeholder=""
              label="Server Name:"
            />
            <Field
              name="serverIpAddress"
              component={InputField}
              type="text"
              placeholder=""
              label="Server IP Address:"
            />
            <Field
              name="centraIpAddress"
              component={InputField}
              type="text"
              placeholder=""
              label="Centreon Central IP Address:"
            />
            <Field
              name="databaseUser"
              component={InputField}
              type="text"
              placeholder=""
              label="Database user:"
            />
            <Field
              name="databasePassword"
              component={InputField}
              type="text"
              placeholder=""
              label="Database password:"
            />
            <Field name="checkbox" component={CheckboxField} label="Centreon must connect to poller to open Broker flow" />
            <div class="form-buttons">
              <Link className="button" to={routeMap.serverConfigurationWizard}>Back</Link>
              <button className="button" type="submit">Apply</button>
            </div>
            {error ? <div class="error-block">{error.message}</div> : null}
          </form>
        </div>
      </div>
    );
  }
}

const validate = (server) => ({
  serverName: serverNameValidator(server.serverName),
  serverIpAddress: serverIpAddressValidator(server.serverIpAddress),
  centraIpAddress: centraIpAddressValidator(server.centraIpAddress),
  databaseUser: databaseUserValidator(server.databaseUser),
  databasePassword: databasePasswordValidator(server.databasePassword)
});

export default connectForm({
  form: 'PollerFormStepOne',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(PollerFormStepOne);
