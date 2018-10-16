import React, { Component } from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import InputField from "../../form-fields/InputField";
import SelectField from "../../form-fields/SelectField";
import RadioField from "../../form-fields/PreselectedRadioField";
import {Translate} from 'react-redux-i18n';
import {I18n} from "react-redux-i18n";

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

  onManualInputChanged (inputTypeManual) {
    this.setState({
      inputTypeManual: inputTypeManual
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
            <h2 className="form-title mb-2"><Translate value="Remote Server Configuration"/></h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>


            <Field
              name="inputTypeManual"
              onChange={() => { this.onManualInputChanged(true) }}
              checked={inputTypeManual}
              component={RadioField}
              label={I18n.t("Create new Remote Server")}
            />
            {inputTypeManual ? (
              <div>
                <Field
                  name="server_name"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={I18n.t("Server Name") + ":"}
                />
                <Field
                  name="server_ip"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={I18n.t("Server IP address") + ":"}
                />
                <Field
                    name="db_user"
                    component={InputField}
                    type="text"
                    placeholder=""
                    label={I18n.t("Database username") + ":"}
                />
                <Field
                    name="db_password"
                    component={InputField}
                    type="password"
                    placeholder=""
                    label={I18n.t("Database password") + ":"}
                />
                <Field
                  name="centreon_central_ip"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={I18n.t("Centreon Central IP address, as seen by this server") + ":"}
                />
                <Field
                  name="centreon_folder"
                  component={InputField}
                  type="text"
                  placeholder="/centreon/"
                  label={I18n.t("Centreon Web Folder on Remote") + ":"}
                />
              </div>
            ) : null}


            <Field
              name="inputTypeManual"
              onClick={() => { this.onManualInputChanged(false) }}
              checked={!inputTypeManual}
              component={RadioField}
              label={I18n.t("Select a Remote Server") + ":"}
            />
            {!inputTypeManual ? (
              <div>
                {waitList ? (
                  <Field
                    name="server_ip"
                    component={SelectField}
                    label={I18n.t("Select Pending Remote Links") + ":"}
                    required
                    options={[
                      {
                        disabled: true,
                        selected: true,
                        text: I18n.t("Select IP"),
                        value: ""
                      }
                    ].concat(waitList.map(c => ({ value: c.id, text: c.ip })))}
                  />
                ) : null}
                <Field
                  name="server_name"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={I18n.t("Server Name") + ":"}
                />
                <Field
                  name="db_user"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={I18n.t("Database username") + ":"}
                />
                <Field
                  name="db_password"
                  component={InputField}
                  type="password"
                  placeholder=""
                  label={I18n.t("Database password") + ":"}
                />
                <Field
                  name="centreon_central_ip"
                  component={InputField}
                  type="text"
                  placeholder=""
                  label={I18n.t("Centreon Central IP address, as seen by this server") + ":"}
                />
                <Field
                  name="centreon_folder"
                  component={InputField}
                  type="text"
                  placeholder="/centreon/"
                  label={I18n.t("Centreon Web Folder on Remote") + ":"}
                />
              </div>
            ) : null}



            <div class="form-buttons">
              <button className="button" type="submit">
                <Translate value="Next"/>
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
  server_name: I18n.t(serverNameValidator(server_name)),
  server_ip: I18n.t(serverIpAddressValidator(server_ip)),
  centreon_central_ip: I18n.t(centralIpAddressValidator(centreon_central_ip)),
  db_user: I18n.t(databaseUserValidator(db_user)),
  db_password: I18n.t(databasePasswordValidator(db_password)),
  centreon_folder: I18n.t(centreonPathValidator(centreon_folder))
});

export default connectForm({
  form: "RemoteServerFormStepOne",
  validate,
  warn: () => {},
  enableReinitialize: true,
  destroyOnUnmount: false,
  keepDirtyOnReinitialize: true
})(RemoteServerFormStepOne);
