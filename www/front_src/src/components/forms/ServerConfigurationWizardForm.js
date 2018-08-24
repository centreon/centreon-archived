import React from 'react';
import {Field, reduxForm as connectForm} from 'redux-form';

import RadioGroupFields from '../form-fields/RadioGroupFields';

const configurationTypes = [
  {
    label: 'Add a Centreon Remote Server',
    value: 1,
  },
  {
    label: 'Add a Centreon Poller',
    value: 2,
  }
];

const ServerConfigurationWizardForm = ({error, handleSubmit, onSubmit, submitting, disabled}) => (
  <div className="form-wrapper small">
    <div className="form-inner">
      <div className="form-heading">
        <h2 className="form-title">Server Configuration Wizard</h2>
        <p className="form-text">Choose a configuration type:</p>
      </div>
      <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field name="server_type" component={RadioGroupFields} options={configurationTypes} />
        <div class="form-buttons">
          <button disabled={disabled} className="button" type="submit">Next</button>
        </div>
        {error ? <div class="error-block">{error.message}</div> : null}
      </form>
    </div>
  </div>
);

const validate = () => ({});

export default connectForm({form: 'ServerConfigurationWizardForm', validate})(ServerConfigurationWizardForm);
