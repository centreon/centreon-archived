import React, { Component } from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import classnames from 'classnames';
import styles from '../../../styles/partials/form/_form.scss';
import InputField from "../../form-fields/InputField";
import SelectField from "../../form-fields/SelectField";
import RadioField from "../../form-fields/PreselectedRadioField";
import CheckboxField from "../../form-fields/CheckboxField";
import { Translate, I18n } from 'react-redux-i18n';

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
      <div className={styles["form-wrapper"]}>
        <div className={styles["form-inner"]}>
          <div className={styles["form-heading"]}>
            <h2 className={classnames(styles["form-title"], styles["mb-2"])}>
              <Translate value="Remote Server Configuration"/>
            </h2>
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
                <Field
                  name="no_check_certificate"
                  component={CheckboxField}
                  label={I18n.t("Do not check SSL certificate validation")}
                />
                <Field
                  name="no_proxy"
                  component={CheckboxField}
                  label={I18n.t("Do not use configured proxy to connect to this server")}
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
                <Field
                  name="no_check_certificate"
                  component={CheckboxField}
                  label={I18n.t("Do not check SSL certificate validation")}
                />
                <Field
                  name="no_proxy"
                  component={CheckboxField}
                  label={I18n.t("Do not use configured proxy to connect to this server")}
                />
              </div>
            ) : null}

            <div className={styles["form-buttons"]}>
              <button className={styles["button"]} type="submit">
                <Translate value="Next"/>
              </button>
            </div>
            {error ? <div className={styles["error-block"]}>{error.message}</div> : null}
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
