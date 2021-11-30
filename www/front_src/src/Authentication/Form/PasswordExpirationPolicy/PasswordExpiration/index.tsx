import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext } from 'formik';

import { FormHelperText, makeStyles, Typography } from '@material-ui/core';

import TimeInput from '../../../TimeInput';
import {
  labelMonth,
  labelMonths,
  labelPasswordExpiration,
  labelDay,
  labelDays,
} from '../../../translatedLabels';
import { SecurityPolicy } from '../../../models';
import { getField } from '../../utils';

const passwordExpirationFieldName = 'passwordExpiration';

const useStyles = makeStyles((theme) => ({
  passwordExpirationContainer: {
    marginTop: theme.spacing(1),
  },
  timeInputs: {
    columnGap: theme.spacing(1.5),
    display: 'flex',
    flexDirection: 'row',
    marginBottom: theme.spacing(0.5),
    marginTop: theme.spacing(0.5),
  },
}));

const PasswordExpiration = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { values, setFieldValue, errors } = useFormikContext<SecurityPolicy>();

  const change = (value: number): void => {
    setFieldValue(passwordExpirationFieldName, value);
  };

  const passwordExpirationValue = React.useMemo<number>(
    () => getField({ field: passwordExpirationFieldName, object: values }),
    [values],
  );

  const passwordExpirationError = React.useMemo<number>(
    () => getField({ field: passwordExpirationFieldName, object: errors }),
    [errors],
  );

  return (
    <div className={classes.passwordExpirationContainer}>
      <Typography variant="h6">{t(labelPasswordExpiration)}</Typography>
      <div className={classes.timeInputs}>
        <TimeInput
          getAbsoluteValue
          labels={{
            plural: labelMonths,
            singular: labelMonth,
          }}
          name={`${passwordExpirationFieldName}_${labelMonth}`}
          timeValue={passwordExpirationValue}
          unit="months"
          onChange={change}
        />
        <TimeInput
          labels={{
            plural: labelDays,
            singular: labelDay,
          }}
          name={`${passwordExpirationFieldName}_${labelDay}`}
          timeValue={passwordExpirationValue}
          unit="days"
          onChange={change}
        />
      </div>
      {passwordExpirationError && (
        <FormHelperText error>{passwordExpirationError}</FormHelperText>
      )}
    </div>
  );
};

export default PasswordExpiration;
