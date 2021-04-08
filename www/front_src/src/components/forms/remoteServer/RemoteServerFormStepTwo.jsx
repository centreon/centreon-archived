/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import { Field, reduxForm as connectForm } from 'redux-form';
import Select from 'react-select';
import { withTranslation } from 'react-i18next';

import styles from '../../../styles/partials/form/_form.scss';
import fieldHoc from '../../form-fields/hoc';

class RemoteServerFormStepTwo extends Component {
  state = {
    value: [],
  };

  handleChange = (event, values) => {
    this.setState({ value: values });
  };

  render() {
    const { error, handleSubmit, onSubmit, pollers, t } = this.props;
    const { value } = this.state;

    return (
      <div className={styles['form-wrapper']}>
        <div className={styles['form-inner']}>
          <div className={styles['form-heading']}>
            <h2 className={styles['form-title']}>
              {t('Select pollers to be attached to this new Remote Server')}
            </h2>
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
              <button className={styles.button} type="submit">
                {t('Apply')}
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

export default withTranslation()(
  connectForm({
    destroyOnUnmount: false,
    enableReinitialize: true,
    form: 'RemoteServerFormStepTwo',
    keepDirtyOnReinitialize: true,
  })(RemoteServerFormStepTwo),
);
