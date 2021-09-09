/* eslint-disable consistent-return */
/* eslint-disable array-callback-return */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';

import { Field, reduxForm as connectForm } from 'redux-form';
import { withTranslation } from 'react-i18next';
import Select from 'react-select';

import { Paper, Typography, Button } from '@material-ui/core';

import styles from '../../../styles/partials/form/_form.scss';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../../form-fields/CheckboxField';
import fieldHoc from '../../form-fields/hoc';

class PollerFormStepTwo extends Component {
  state = {
    selectedAdditionals: [],
    selectedMaster: null,
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
  };

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
      selectedAdditionals: filteredAdditionals,
      selectedMaster: value,
    });
  };

  /**
   * Update selected additionals in state
   */
  handleChangeAdditionals = (event, values) => {
    this.setState({
      selectedAdditionals: values,
    });
  };

  render() {
    const { error, handleSubmit, onSubmit, pollers, t } = this.props;
    const { selectedMaster } = this.state;

    const availableAdditionals = this.getAvailableAdditionals();

    return (
      <Paper className={styles['form-container']}>
        <div className={styles['form-inner']}>
          <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
            {pollers.length ? (
              <>
                <Typography variant="h6">
                  {t('Attach poller to a master remote server')}
                </Typography>
                <Field
                  component={SelectField}
                  name="linked_remote_master"
                  options={[
                    {
                      text: '',
                      value: null,
                    },
                  ].concat(
                    pollers.map((c) => ({
                      label: c.name,
                      text: c.name,
                      value: c.id,
                    })),
                  )}
                  value={selectedMaster}
                  onChange={this.handleChangeMaster}
                />
              </>
            ) : null}
            {selectedMaster && pollers.length >= 2 ? (
              <>
                <Typography variant="h6">
                  {t('Attach poller to additional remote servers')}
                </Typography>
                <div className={styles['form-item']}>
                  <Field
                    isMulti
                    component={fieldHoc(Select)}
                    name="linked_remote_slaves"
                    options={availableAdditionals.map((remote) => ({
                      label: remote.name,
                      value: remote.id,
                    }))}
                    onChange={this.handleChangeAdditionals}
                  />
                </div>
              </>
            ) : null}
            <Field
              component={CheckboxField}
              defaultValue={false}
              label={t('Advanced: reverse Centreon Broker communication flow')}
              name="open_broker_flow"
            />
            <div className={styles['form-buttons']}>
              <Button
                color="primary"
                size="small"
                type="submit"
                variant="contained"
              >
                {t('Apply')}
              </Button>
            </div>
            {error ? (
              <Typography style={{ color: '#d0021b' }} variant="body2">
                {error.message}
              </Typography>
            ) : null}
          </form>
        </div>
      </Paper>
    );
  }
}

export default withTranslation()(
  connectForm({
    destroyOnUnmount: false,
    enableReinitialize: true,
    form: 'PollerFormStepTwo',
    keepDirtyOnReinitialize: true,
  })(PollerFormStepTwo),
);
