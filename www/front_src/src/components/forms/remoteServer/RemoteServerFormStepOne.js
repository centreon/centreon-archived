import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import InputField from '../../form-fields/InputField';

class RemoteServerFormStepOne extends Component {

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
        <Field
          name="databaseUser"
          component={InputField}
          type="text"
          placeholder=""
          label="Database user:"
        />
        <Field
          name="databasePassword"
          component={InputField}
          type="text"
          placeholder=""
          label="Database password:"
        />
        <button
          type="submit"
        >Next</button>
        {error ? <div class="error-block">{error.message}</div> : null}
      </form>
    );
  }
}

const validate = () => ({});

export default connectForm({
  form: 'RemoteServerFormStepOne',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(RemoteServerFormStepOne);
