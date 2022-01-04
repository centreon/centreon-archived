/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import { Field, reduxForm as connectForm } from 'redux-form';
import Select from 'react-select';
import { withTranslation } from 'react-i18next';
import { Typography, Button } from '@mui/material';

import styles from '../../../styles/partials/form/_form.scss';
import fieldHoc from '../../../components/form-fields/hoc';

class RemoteServerFormStepTwo extends Component {
  state = {
    value: [],
  };

  handleChange = (event, values) => {
    this.setState({ value: values });
  };

  render() {
    const { error, handleSubmit, onSubmit, pollers, t, goToPreviousStep } =
      this.props;
    const { value } = this.state;

    return (
      <div>
        <div className={styles['form-heading']}>
          <Typography variant="h6">
            {t('Select pollers to be attached to this new Remote Server')}
          </Typography>
        </div>
        <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
          {pollers ? (
            <Field
              isMulti
              multi
              component={fieldHoc(Select)}
              label={`${t('Select linked Remote Server')}:`}
              name="linked_pollers"
              options={pollers.items.map((c) => ({
                label: c.text,
                value: c.id,
              }))}
              value={value}
              onChange={this.handleChange}
            />
          ) : null}
          <div className={styles['form-buttons']}>
            <Button size="small" onClick={goToPreviousStep}>
              {t('Previous')}
            </Button>
            <Button
              color="primary"
              size="small"
              type="submit"
              variant="contained"
              onClick={handleSubmit}
            >
              {t('Apply')}
            </Button>
          </div>
          {error && (
            <Typography color="error" variant="body2">
              {error.message}
            </Typography>
          )}
        </form>
      </div>
    );
  }
}

export default withTranslation()(
  connectForm({
    destroyOnUnmount: false,
    enableReinitialize: true,
    form: 'RemoteServerFormStepTwo',
    keepDirtyOnReinitialize: true,
  })(RemoteServerFormStepTwo),
);
