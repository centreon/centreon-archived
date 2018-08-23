import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import InputField from '../../form-fields/InputField';

class PollerFormStepOne extends Component {

  render() {
    const {error, handleSubmit, onSubmit, submitting} = this.props;
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
  form: 'PollerFormStepOne',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(PollerFormStepOne);
