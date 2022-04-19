/* eslint-disable jsx-a11y/label-has-for */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import classnames from 'classnames';

import { FormControlLabel, Radio, Typography } from '@mui/material';

import styles from '../../styles/partials/form/_form.scss';

import fieldHoc from './hoc';

const RadioField = ({ checked, error, label, info, className, ...rest }) => (
  <div
    className={classnames(
      styles['custom-control'],
      styles['custom-radio'],
      styles['form-group'],
    )}
  >
    <FormControlLabel
      checked={checked}
      control={<Radio color="primary" size="small" />}
      label={label}
      onChange={rest.onChange}
      onClick={rest.onClick}
    />

    {error ? (
      <div className={styles['invalid-feedback']}>
        <Typography color="error" variant="body2">
          {error}
        </Typography>
      </div>
    ) : null}
  </div>
);

RadioField.displayName = 'RadioField';
RadioField.defaultProps = { className: styles.field };

export { RadioField };

export default fieldHoc(RadioField);
