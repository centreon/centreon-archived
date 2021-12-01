import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import { isEmpty, isNil, not, path } from 'ramda';

import { makeStyles, useTheme } from '@material-ui/core';

import { TextField } from '@centreon/ui';

import {
  labelGood,
  labelNumberOfAttemptsBeforeBlockNewAttempt,
  labelStrong,
  labelUnknown,
  labelWeak,
} from '../../translatedLabels';
import { getField } from '../utils';
import StrengthProgress from '../../StrengthProgress';

const attemptsFieldName = 'attempts';

const useStyles = makeStyles({
  input: {
    width: '65%',
  },
});

const Attempts = (): JSX.Element => {
  const classes = useStyles();
  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();
  const { t } = useTranslation();
  const theme = useTheme();

  const changeInput = (event: React.ChangeEvent<HTMLInputElement>): void => {
    const value = path(['target', 'value'], event);

    if (isEmpty(value)) {
      setFieldValue(attemptsFieldName, null);

      return;
    }

    setFieldValue(attemptsFieldName, value);
  };

  const attemptsError = React.useMemo(
    () =>
      getField<string | undefined>({
        field: attemptsFieldName,
        object: errors,
      }),
    [errors],
  );

  const attemptsValue = React.useMemo(
    () =>
      getField<number>({
        field: attemptsFieldName,
        object: values,
      }),
    [values],
  );

  const thresholds = React.useMemo(
    () => [
      { color: theme.palette.error.main, label: labelWeak, value: 3 },
      { color: theme.palette.warning.main, label: labelGood, value: 6 },
      { color: theme.palette.success.main, label: labelStrong, value: 8 },
      { color: theme.palette.grey[500], label: labelUnknown, value: 11 },
    ],
    [theme],
  );

  const displayStrengthProgress =
    isNil(attemptsError) && not(isNil(attemptsValue));

  return (
    <div className={classes.input}>
      <TextField
        fullWidth
        error={attemptsError}
        helperText={attemptsError}
        inputProps={{
          min: 1,
        }}
        label={t(labelNumberOfAttemptsBeforeBlockNewAttempt)}
        name={attemptsFieldName}
        type="number"
        value={attemptsValue || ''}
        onChange={changeInput}
      />
      {displayStrengthProgress && (
        <StrengthProgress
          max={10}
          thresholds={thresholds}
          value={attemptsValue || 0}
        />
      )}
    </div>
  );
};

export default Attempts;
