/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */
/* eslint-disable import/no-named-as-default */

import React, { Component } from 'react';
import { Field, reduxForm as connectForm } from 'redux-form';
import { Translate, I18n } from 'react-redux-i18n';
import styles from '../../../styles/partials/form/_form.scss';
import SelectField from '../../form-fields/SelectField';
import CheckboxField from '../../form-fields/CheckboxField';

class PollerFormStepTwo extends Component {
  render() {
    const { error, handleSubmit, onSubmit, pollers } = this.props;

    return (
      <div className={styles['form-wrapper']}>
        <div className={styles['form-inner']}>
          <div className={styles['form-heading']}>
            <h2 className={styles['form-title']}>
              <Translate value="Attach poller to a server" />
            </h2>
          </div>
          <form autoComplete="off" onSubmit={handleSubmit(onSubmit)}>
            {pollers ? (
              <Field
                name="linked_remote"
                component={SelectField}
                options={[
                  {
                    disabled: true,
                    selected: true,
                    text: `${I18n.t('Select a Remote Server')}`,
                    value: '',
                  },
                ].concat(
                  pollers.map((c) => ({
                    value: c.id,
                    label: c.name,
                    text: c.name,
                  })),
                )}
              />
            ) : null}
            <Field
              name="open_broker_flow"
              component={CheckboxField}
              label={I18n.t(
                'Advanced: reverse Centreon Broker communication flow',
              )}
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
