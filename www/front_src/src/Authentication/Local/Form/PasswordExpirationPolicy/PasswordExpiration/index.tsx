import { useMemo } from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import dayjs from 'dayjs';
import { lte } from 'ramda';

import { FormHelperText, FormLabel } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';

import { labelPasswordExpiresAfter } from '../../../translatedLabels';
import { getField } from '../../utils';
import TimeInputs from '../../../TimeInputs';
import { TimeInputConfiguration } from '../../../models';
import { twelveMonths } from '../../../timestamps';

import ExcludedUsers from './ExcludedUsers';

const passwordExpirationFieldName = 'passwordExpiration.expirationDelay';

const useStyles = makeStyles((theme) => ({
  container: {
    alignItems: 'flex-end',
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
  },
  passwordExpiration: {
    marginTop: theme.spacing(1),
  },
}));

const PasswordExpiration = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

  const change = (value: number): void => {
    setFieldValue(passwordExpirationFieldName, value || null);
  };

  const passwordExpirationValue = getField<number>({
    field: passwordExpirationFieldName,
    object: values,
  });

  const passwordExpirationError = getField<string>({
    field: passwordExpirationFieldName,
    object: errors,
  });

  const minDaysOption = useMemo(
    (): number | undefined =>
      lte(
        dayjs.duration({ months: 1 }).asMilliseconds(),
        passwordExpirationValue,
      )
        ? undefined
        : 7,
    [passwordExpirationValue],
  );

  const maxDaysOption = useMemo(
    (): number | undefined =>
      lte(
        dayjs.duration({ years: 1 }).asMilliseconds(),
        passwordExpirationValue,
      )
        ? 0
        : undefined,
    [passwordExpirationValue],
  );

  const timeInputConfiguration: Array<TimeInputConfiguration> = [
    { unit: 'months' },
    { maxOption: maxDaysOption, minOption: minDaysOption, unit: 'days' },
  ];

  return useMemoComponent({
    Component: (
      <div className={classes.container}>
        <div className={classes.passwordExpiration}>
          <FormLabel>{t(labelPasswordExpiresAfter)}</FormLabel>
          <TimeInputs
            baseName={passwordExpirationFieldName}
            inputLabel={labelPasswordExpiresAfter}
            maxDuration={twelveMonths}
            timeInputConfigurations={timeInputConfiguration}
            timeValue={passwordExpirationValue}
            onChange={change}
          />
          {passwordExpirationError && (
            <FormHelperText error>{passwordExpirationError}</FormHelperText>
          )}
        </div>
        <div>
          <ExcludedUsers />
        </div>
      </div>
    ),
    memoProps: [passwordExpirationValue, passwordExpirationError],
  });
};

export default PasswordExpiration;
