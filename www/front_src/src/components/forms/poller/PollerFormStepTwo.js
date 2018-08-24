import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../../form-fields/CheckboxField';

import { selectRemoteServerValidator } from '../../../helpers/validators';

class PollerFormStepTwo extends Component {

  render() {
    const {error, handleSubmit, onSubmit, submitting} = this.props;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title">Server Configuration</h2>
          </div>
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
            <div class="form-buttons">
              <button type="submit">Back</button>
              <button type="submit">Apply</button>
            </div>
            {error ? <div class="error-block">{error.message}</div> : null}
          </form>
        </div>
      </div>
    );
  }
}

const validate = (server) => ({
  selectRemoteServer: selectRemoteServerValidator(server.selectRemoteServer)
});

export default connectForm({
  form: 'PollerFormStepTwo',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(PollerFormStepTwo);
