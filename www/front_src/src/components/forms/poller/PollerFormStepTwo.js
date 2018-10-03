import React, { Component } from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import SelectField from "../../form-fields/SelectField";
import CheckboxField from "../../form-fields/CheckboxField";

class PollerFormStepTwo extends Component {
  render() {
    const { error, handleSubmit, onSubmit, pollers } = this.props;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title">Attach poller to a server</h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
            {pollers ? (
              <Field
                name="linked_remote"
                component={SelectField}
                label="Select:"
                options={[
                  {
                    disabled: true,
                    selected: true,
                    text: "Select Remote Server",
                    value: ""
                  }
                ].concat(
                  pollers.map(c => ({ value: c.id, label: c.name, text:c.name }))
                )}
              />
            ) : null}
            <Field
              name="open_broker_flow"
              component={CheckboxField}
              label="Advanced: reverse Centreon Broker communication flow"
            />
            <div class="form-buttons">
              <button className="button" type="submit">
                Apply
              </button>
            </div>
            {error ? <div class="error-block">{error.message}</div> : null}
          </form>
        </div>
      </div>
    );
  }
}

const validate = () => ({});

export default connectForm({
  form: "PollerFormStepTwo",
  validate,
  warn: () => {},
  enableReinitialize: true
})(PollerFormStepTwo);
