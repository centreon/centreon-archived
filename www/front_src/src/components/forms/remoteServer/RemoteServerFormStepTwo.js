import React, { Component } from "react";
import { Field, reduxForm as connectForm } from "redux-form";
import MultiSelect from "../../form-fields/MultiSelect";
import Select from "react-select";
import fieldHoc from "../../form-fields/hoc";

class RemoteServerFormStepTwo extends Component {
  state = {
    value:[]
  }
  handleChange = (e,values) => {
    this.setState({value:values})
  }
  render() {
    const { error, handleSubmit, onSubmit, pollers } = this.props;
    const {value} = this.state;
    return (
      <div className="form-wrapper">
        <div className="form-inner">
          <div className="form-heading">
            <h2 className="form-title">Select pollers to be attached to this new Remote Server</h2>
          </div>
          <form autocomplete="off" onSubmit={handleSubmit(onSubmit)}>
            {pollers ? (
              <Field
                name="linked_pollers"
                component={fieldHoc(Select)}
                label="Select linked Remote Server:"
                options={pollers.items.map(c => ({
                  value: c.id,
                  label: c.text
                }))}
                value={value}
                onChange={this.handleChange}
                multi={true}
                isMulti={true}
              />
            ) : null}
            {/* <Field
              name="manage_broker_config"
              component={CheckboxField}
              label="Manage automatically Centreon Broker Configuration of selected poller?"
            /> */}
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
  form: "RemoteServerFormStepTwo",
  validate,
  warn: () => { },
  enableReinitialize: true
})(RemoteServerFormStepTwo);
