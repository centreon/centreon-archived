import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import Button from '../../button/index';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../form-fields/CheckboxField';

class PollerFormStepTwo extends Component {

  render() {
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
        <Button
          type="submit"
          buttonClass={''}
          buttonTitle={'Apply'}
        />
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
