import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../../form-fields/CheckboxField';

class PollerFormStepTwo extends Component {

  render() {
    const {error, handleSubmit, onSubmit, submitting} = this.props;
    return (
      <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field
          name="selectRemoteServer"
          component={SelectField}
          label="Select linked Remote Server:"
          required
          options={[]}
        />
        <br />
        <Field name="checkbox" component={CheckboxField} label="Centreon must connect to poller to open Broker flow" />
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
  form: 'PollerFormStepTwo',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(PollerFormStepTwo);
