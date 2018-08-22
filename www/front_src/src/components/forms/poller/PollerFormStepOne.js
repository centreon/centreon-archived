import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import Button from '../../button/index';
import InputField from '../../form-fields/InputField';

class PollerFormStepOne extends Component {

  render() {
    return (
      <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
        <Field
          name="serverName"
          component={InputField}
          type="text"
          placeholder=""
          label="Server Name:"
        />
        <Field
          name="serverIpAddress"
          component={InputField}
          type="text"
          placeholder=""
          label="Server IP Address:"
        />
        <Field
          name="centraIpAddress"
          component={InputField}
          type="text"
          placeholder=""
          label="Centreon Central IP Address:"
        />
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
  form: 'PollerFormStepOne',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(PollerFormStepOne);
