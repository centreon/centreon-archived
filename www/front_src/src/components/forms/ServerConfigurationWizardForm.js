import React from 'react';
import {Field, reduxForm as connectForm} from 'redux-form';

import RadioGroupFields from '../form-fields/RadioGroupFields';

const configurationTypes = [
  {
    label: 'Add a Centreon Poller',
    value: 1,
  },
  {
    label: 'Add a Centreon Remote Server',
    value: 2,
  }
];

const ServerConfigurationWizardForm = ({error, handleSubmit, onSubmit, submitting}) => (
  <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
    <Field name="userTypeId" component={RadioGroupFields} options={configurationTypes} />
    {error ? <div class="error-block">{error.message}</div> : null}
  </form>
);

const validate = () => ({});

export default connectForm({form: 'ServerConfigurationWizardForm', validate})(ServerConfigurationWizardForm);
