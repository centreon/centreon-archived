import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import dayjs from 'dayjs';
import { lte } from 'ramda';

import { FormHelperText, FormLabel } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import { labelPasswordExpiration } from '../../translatedLabels';
import { getField } from '../utils';
import TimeInputs from '../../TimeInputs';
import { TimeInputConfiguration } from '../../models';
import { sevenDays, twelveMonths } from '../../timestamps';

const passwordExpirationFieldName = 'passwordExpiration';

const useStyles = makeStyles((theme) => ({
  passwordExpirationContainer: {
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

  const minDaysOption = React.useMemo(
    (): number | undefined =>
      lte(
        dayjs.duration({ months: 1 }).asMilliseconds(),
        passwordExpirationValue,
      )
        ? undefined
        : 7,
    [passwordExpirationValue],
  );

  const maxDaysOption = React.useMemo(
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
      <div className={classes.passwordExpirationContainer}>
        <FormLabel>{t(labelPasswordExpiration)}</FormLabel>
        <TimeInputs
          baseName={passwordExpirationFieldName}
          inputLabel={labelPasswordExpiration}
          maxDuration={twelveMonths}
          timeInputConfigurations={timeInputConfiguration}
          timeValue={passwordExpirationValue}
          onChange={change}
        />
        {passwordExpirationError && (
          <FormHelperText error>{passwordExpirationError}</FormHelperText>
        )}
      </div>
    ),
    memoProps: [passwordExpirationValue, passwordExpirationError],
  });
};

export default PasswordExpiration;
