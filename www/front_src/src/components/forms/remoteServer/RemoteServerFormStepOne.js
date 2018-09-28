import React, { Component } from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import InputField from "../../form-fields/InputField";
import SelectField from "../../form-fields/SelectField";
import RadioField from "../../form-fields/PreselectedRadioField";
import { initialize } from 'redux-form';

import {
  serverNameValidator,
  serverIpAddressValidator,
  centralIpAddressValidator,
  databaseUserValidator,
  databasePasswordValidator,
  centreonPathValidator
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

  UNSAFE_componentWillReceiveProps = nextProps => {
    const { waitList } = nextProps;
    const { initialized } = this.state;
    if (waitList && !initialized) {
     this.initializeFromRest(waitList.length > 0);
     //this.initializeFromRest(true);//set to true of false if abandon the upper case condition
    }
    this.setState({
      initialized: true,
      centreon_folder: '/centreon/'
    });
  };

  render() {
    const { error, handleSubmit, onSubmit, waitList } = this.props;
    const { inputTypeManual } = this.state;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title mb-2">Remote Server Configuration</h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>


            <Field
              name="inputTypeManual"
              onChange={this.onManualInputChanged.bind(this)}
              checked={inputTypeManual}
              value={true}
              component={RadioField}
              label="Create new Remote Server"
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
                  label="Server IP address:"
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
                  label="Centreon Central IP address, as seen by this server:"
                />
                <Field
                  name="centreon_folder"
                  component={InputField}
                  type="text"
                  placeholder="/centreon/"
                  label="Centreon Web Folder on Remote:"
                />
              </div>
            ) : null}


            <Field
              name="inputTypeManual"
              onClick={this.onManualInputChanged.bind(this)}
              checked={!inputTypeManual}
              value={false}
              component={RadioField}
              label="Select a Remote Server"
            />
            {!inputTypeManual ? (
              <div>
                {waitList ? (
                  <Field
                    name="server_ip"
                    component={SelectField}
                    label="Select Pending Remote Links:"
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
                  label="Database username:"
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
                  label="Centreon Central IP address, as seen by this server:"
                />
                <Field
                  name="centreon_folder"
                  component={InputField}
                  type="text"
                  placeholder="/centreon/"
                  label="Centreon Web Folder on Remote:"
                />
              </div>
            ) : null}


          
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
  db_password,
  centreon_folder
}) => ({
  server_name: serverNameValidator(server_name),
  server_ip: serverIpAddressValidator(server_ip),
  centreon_central_ip: centralIpAddressValidator(centreon_central_ip),
  db_user: databaseUserValidator(db_user),
  db_password: databasePasswordValidator(db_password),
  centreon_folder: centreonPathValidator(centreon_folder)
});

export default connectForm({
  form: "RemoteServerFormStepOne",
  validate,
  warn: () => {},
  enableReinitialize: true
})(RemoteServerFormStepOne);
