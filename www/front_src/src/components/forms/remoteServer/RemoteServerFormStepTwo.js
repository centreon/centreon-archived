import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../../form-fields/CheckboxField';

import { selectRemoteServerValidator } from '../../../helpers/validators';

class RemoteServerFormStepTwo extends Component {

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
              name="selectDistantPollers"
              component={SelectField}
              label="Select linked Distant Pollers:"
              required
              options={[]}
            />
            <br />
            <Field name="checkbox" component={CheckboxField} label="Manage automatically Centreon Broker Configuration of selected poller?" />
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
  form: 'RemoteServerFormStepTwo',
  validate,
  warn: () => { },
  enableReinitialize: true,
})(RemoteServerFormStepTwo);
