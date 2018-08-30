import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import InputField from '../../form-fields/InputField';
import CheckboxField from '../../form-fields/CheckboxField';
import SelectField from '../../form-fields/SelectField';
import RadioField from '../../form-fields/RadioField';

import { 
  serverNameValidator, 
  serverIpAddressValidator, 
  centralIpAddressValidator,
  databaseUserValidator,
  databasePasswordValidator
} from '../../../helpers/validators';

class RemoteServerFormStepOne extends Component {
  state = {
    manualInput: false,
    autoInput: false
  }

  manualInputToggle = () => {
    const { manualInput } = this.state;
    this.setState({
      manualInput: !manualInput,
      autoInput: false,
    })
  }

  autoInputToggle = () => {
    const { autoInput } = this.state;
    this.setState({
      autoInput: !autoInput,
      manualInput: false
    })
  }
  
  render() {
    const {error, handleSubmit, onSubmit, submitting} = this.props;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title mb-2">Server Configuration</h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
            <Field name="radio" onClick={this.manualInputToggle.bind(this)} component={RadioField} label="Manual input" />
              {this.state.manualInput && 
                <div>
                  <Field
                    name="server_name"
                    component={InputField}
                    type="text"
                    placeholder=""
                    label="Server Name:"
                  />
                  <Field
                    name="server_ip"
                    component={InputField}
                    type="text"
                    placeholder=""
                    label="Server IP Address:"
                  />
                  <Field
                    name="centreon_central_ip"
                    component={InputField}
                    type="text"
                    placeholder=""
                    label="Centreon Central IP Address:"
                  />
                </div>
              }
            <Field name="radio" onClick={this.autoInputToggle.bind(this)} component={RadioField} label="Please select a server" />
              {this.state.autoInput && 
                <div>
                  <Field
                    name="open_broker_flow"
                    component={SelectField}
                    label="Select linked Distant Pollers:"
                    required
                    options={['One', 'Two', 'Three', 'Four']}
                  />
                  {/* <MultiSelect options={waitList.map(wl => ({value: wl.id, label: wl.name}))} /> */}
                </div>
              }
            <Field name="checkbox" component={CheckboxField} label="Manage automatically Centreon Broker Configuration of selected poller?" />
            <div class="form-buttons">
              <button className="button" type="submit">Next</button>
            </div>
            {error ? <div class="error-block">{error.message}</div> : null}
          </form>
        </div>
      </div>
    );
  }
}

const validate = (server) => ({
  server_name: serverNameValidator(server.server_name),
  server_ip: serverIpAddressValidator(server.server_ip),
  centreon_central_ip: centralIpAddressValidator(server.centreon_central_ip),
  db_user: databaseUserValidator(server.db_user),
  db_password: databasePasswordValidator(server.db_password)
});

export default connectForm({
  form: 'RemoteServerFormStepOne',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(RemoteServerFormStepOne);
