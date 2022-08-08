import { ChangeEvent, useCallback, useMemo } from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';
import { isEmpty, isNil, not, path } from 'ramda';

import { useTheme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { TextField, useMemoComponent } from '@centreon/ui';

import {
  labelGood,
  labelNumberOfAttemptsBeforeUserIsBlocked,
  labelStrong,
  labelUnknown,
  labelWeak,
} from '../../translatedLabels';
import { getField } from '../utils';
import StrengthProgress from '../../StrengthProgress';

export const attemptsFieldName = 'attempts';

const useStyles = makeStyles((theme) => ({
  input: {
    maxWidth: theme.spacing(43),
  },
}));

const Attempts = (): JSX.Element => {
  const classes = useStyles();
  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();
  const { t } = useTranslation();
  const theme = useTheme();

  const changeInput = useCallback(
    (event: ChangeEvent<HTMLInputElement>): void => {
      const value = path(['target', 'value'], event) as string;

      if (isEmpty(value)) {
        setFieldValue(attemptsFieldName, null);

        return;
      }

      setFieldValue(attemptsFieldName, parseInt(value, 10));
    },
    [attemptsFieldName],
  );

  const attemptsError = getField<string | undefined>({
    field: attemptsFieldName,
    object: errors,
  });

  const attemptsValue = getField<number>({
    field: attemptsFieldName,
    object: values,
  });

  const thresholds = useMemo(
    () => [
      { color: theme.palette.success.main, label: labelStrong, value: 0 },
      { color: theme.palette.warning.main, label: labelGood, value: 3 },
      { color: theme.palette.error.main, label: labelWeak, value: 6 },
      { color: theme.palette.grey[500], label: labelUnknown, value: 11 },
    ],
    [],
  );

  const displayStrengthProgress = useMemo(
    () => isNil(attemptsError) && not(isNil(attemptsValue)),
    [attemptsError, attemptsValue],
  );

  return useMemoComponent({
    Component: (
      <div className={classes.input}>
        <TextField
          fullWidth
          error={attemptsError}
          helperText={attemptsError}
          inputProps={{
            'aria-label': t(labelNumberOfAttemptsBeforeUserIsBlocked),
            min: 1,
          }}
          label={t(labelNumberOfAttemptsBeforeUserIsBlocked)}
          name={attemptsFieldName}
          type="number"
          value={attemptsValue || ''}
          onChange={changeInput}
        />
        {displayStrengthProgress && (
          <StrengthProgress
            isInverted
            max={10}
            thresholds={thresholds}
            value={attemptsValue || 0}
          />
        )}
      </div>
    ),
    memoProps: [attemptsError, attemptsValue],
  });
};

export default Attempts;
