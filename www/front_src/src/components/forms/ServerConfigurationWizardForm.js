import React from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import {Translate} from 'react-redux-i18n';

import RadioGroupFields from "../form-fields/RadioGroupFields";

const configurationTypes = [
  {
    label: "Add a Centreon Remote Server",
    value: 1
  },
  {
    label: "Add a Centreon Poller",
    value: 2
  }
];

const ServerConfigurationWizardForm = ({
  error,
  handleSubmit,
  onSubmit,
  submitting
}) => (
  <div className="form-wrapper small">
    <div className="form-inner">
      <div className="form-heading">
        <h2 className="form-title"><Translate value="Server Configuration Wizard"/></h2>
        <p className="form-text"><Translate value="Choose a server type"/>:</p>
      </div>
      <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field
          name="server_type"
          component={RadioGroupFields}
          options={configurationTypes}
        />
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

const validate = () => ({});

export default connectForm({ form: "ServerConfigurationWizardForm", validate })(
  ServerConfigurationWizardForm
);
