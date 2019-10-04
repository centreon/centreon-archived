/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import { Translate, I18n } from 'react-redux-i18n';
import styles from '../../../styles/partials/form/_form.scss';
import SelectField from "../../form-fields/SelectField";
import CheckboxField from "../../form-fields/CheckboxField";
import Select from "react-select";
import fieldHoc from "../../form-fields/hoc";

class PollerFormStepTwo extends Component {
  state = {
    selectedMaster: null,
    selectedAdditionals: [],
  };

  /**
   * Get available additionals remote server
   * ==> all remote servers except selected master
   */
  getAvailableAdditionals = () => {
    const { pollers } = this.props;
    const { selectedMaster } = this.state;

    const availableAdditionals = pollers.filter((poller) => {
      if (poller.id !== selectedMaster) {
        return true;
      }
    });

    return availableAdditionals;
  }

  /**
   * Update selected master in state
   */
  handleChangeMaster = (event, value) => {
    const { change } = this.props;
    const { selectedAdditionals } = this.state;

    // remove selected additional if it's already the master
    const filteredAdditionals = value
      ? selectedAdditionals.filter((additional) => {
          if (additional.value !== value) {
            return true;
          }
        })
      : [];

    // update field value (mandatory cause it is connected to redux-form)
    change('linked_remote_slaves', filteredAdditionals);

    this.setState({
      selectedMaster: value,
      selectedAdditionals: filteredAdditionals,
    });
  }

  /**
   * Update selected additionals in state
   */
  handleChangeAdditionals = (event, values) => {
    this.setState({
      selectedAdditionals: values
    });
  }

  render() {
    const { error, handleSubmit, onSubmit, pollers } = this.props;
    const { selectedMaster } = this.state;

    const availableAdditionals = this.getAvailableAdditionals();

    return (
      <div className={styles['form-wrapper']}>
        <div className={styles['form-inner']}>
          <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
            {pollers.length ? (
              <>
                <h2 className={styles["form-title"]}>
                  <Translate value="Attach poller to a master remote server"/>
                </h2>
                <Field
                  name="linked_remote_master"
                  component={SelectField}
                  options={[
                    {
                      text: '',
                      value: null,
                    }
                  ].concat(
                    pollers.map(c => ({ value: c.id, label: c.name, text:c.name }))
                  )}
                  value={selectedMaster}
                  onChange={this.handleChangeMaster}
                />
              </>
            ) : null}
            {selectedMaster && pollers.length >= 2 ? (
              <>
                <h2 className={styles["form-title"]}>
                  <Translate value="Attach poller to additional remote servers"/>
                </h2>
                <div className={styles["form-item"]}>
                  <Field
                    name="linked_remote_slaves"
                    component={fieldHoc(Select)}
                    options={availableAdditionals.map(remote => ({
                      value: remote.id,
                      label: remote.name
                    }))}
                    isMulti
                    onChange={this.handleChangeAdditionals}
                  />
                </div>
              </>
            ) : null}
            <Field
              name="open_broker_flow"
              component={CheckboxField}
              label={I18n.t(
                'Advanced: reverse Centreon Broker communication flow',
              )}
              defaultValue={false}
            />
            <div className={styles['form-buttons']}>
              <button className={styles.button} type="submit">
                <Translate value="Apply" />
              </button>
            </div>
            {error ? (
              <div className={styles['error-block']}>{error.message}</div>
            ) : null}
          </form>
        </div>
      </div>
    );
  }
}

const validate = () => ({});

export default connectForm({
  form: 'PollerFormStepTwo',
  validate,
  warn: () => {},
  enableReinitialize: true,
  destroyOnUnmount: false,
  keepDirtyOnReinitialize: true,
})(PollerFormStepTwo);
