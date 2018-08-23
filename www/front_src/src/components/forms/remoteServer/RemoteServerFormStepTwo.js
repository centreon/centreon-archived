import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../../form-fields/CheckboxField';

class RemoteServerFormStepTwo extends Component {

  render() {
    const {error, handleSubmit, onSubmit, submitting} = this.props;
    return (
      <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field
          name="selectDistantPollers"
          component={SelectField}
          label="Select linked Distant Pollers:"
          required
          options={[]}
        />
        <br />
        <Field name="checkbox" component={CheckboxField} label="Manage automatically Centreon Broker Configuration of selected poller?" />
        <button
          type="submit"
        >Apply</button>
        {error ? <div class="error-block">{error.message}</div> : null}
      </form>
    );
  }
}

const validate = () => ({});

export default connectForm({
  form: 'RemoteServerFormStepTwo',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(RemoteServerFormStepTwo);
