import React, { Component } from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import InputField from "../../form-fields/InputField";
import CheckboxField from "../../form-fields/CheckboxField";
import SelectField from "../../form-fields/SelectField";
import RadioField from "../../form-fields/PreselectedRadioField";

import {
  serverNameValidator,
  serverIpAddressValidator,
  centralIpAddressValidator,
  databaseUserValidator,
  databasePasswordValidator
} from "../../../helpers/validators";

class RemoteServerFormStepOne extends Component {
  state = {
    inputTypeManual: true,
    initialized: false
  };

  onManualInputChanged = (e, value) => {
    this.setState({
      inputTypeManual: value
    });
  };

  initializeFromRest = value => {
    this.props.change("inputTypeManual", !value);
    this.setState({
      initialized: true,
      inputTypeManual: !value
    });
  };

  componentWillReceiveProps = nextProps => {
    const { waitList } = nextProps;
    const { initialized } = this.state;
    if (waitList && !initialized) {
      this.initializeFromRest(waitList.length > 0);
    }
  };

  render() {
    const { error, handleSubmit, onSubmit, submitting, waitList } = this.props;
    const { inputTypeManual } = this.state;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title mb-2">Server Configuration</h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
            <Field
              name="inputTypeManual"
              onChange={this.onManualInputChanged.bind(this)}
              checked={inputTypeManual}
              value={true}
              component={RadioField}
              label="Manual input"
            />
            {inputTypeManual ? (
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
            ) : null}
            <Field
              name="inputTypeManual"
              onClick={this.onManualInputChanged.bind(this)}
              checked={!inputTypeManual}
              value={false}
              component={RadioField}
              label="Please select a server"
            />
            {!inputTypeManual ? (
              <div>
                {waitList ? (
                  <Field
                    name="server_ip"
                    component={SelectField}
                    label="Select linked Distant Pollers:"
                    required
                    options={[
                      {
                        disabled: true,
                        selected: true,
                        text: "Select IP",
                        value: ""
                      }
                    ].concat(waitList.map(c => ({ value: c.ip, text: c.ip })))}
                  />
                ) : null}
                <Field
                  name="server_name"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label="Server name:"
                />
                <Field
                  name="db_user"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label="Database user:"
                />
                <Field
                  name="db_password"
                  component={InputField}
                  type="password"
                  placeholder=""
                  label="Database password:"
                />
                <Field
                  name="centreon_central_ip"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label="Centreon Central IP Address:"
                />
              </div>
            ) : null}
            <Field
              name="manage_broker_config"
              component={CheckboxField}
              label="Manage automatically Centreon Broker Configuration of selected poller?"
            />
            <div class="form-buttons">
              <button className="button" type="submit">
                Next
              </button>
            </div>
            {error ? <div class="error-block">{error.message}</div> : null}
          </form>
        </div>
      </div>
    );
  }
}

const validate = ({
  server_name,
  server_ip,
  centreon_central_ip,
  db_user,
  db_password
}) => ({
  server_name: serverNameValidator(server_name),
  server_ip: serverIpAddressValidator(server_ip),
  centreon_central_ip: centralIpAddressValidator(centreon_central_ip),
  db_user: databaseUserValidator(db_user),
  db_password: databasePasswordValidator(db_password)
});

export default connectForm({
  form: "RemoteServerFormStepOne",
  validate,
  warn: () => {},
  enableReinitialize: true
})(RemoteServerFormStepOne);
