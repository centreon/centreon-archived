import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext } from 'formik';

import { FormHelperText, makeStyles, Typography } from '@material-ui/core';

import TimeInput from '../../../TimeInputs/TimeInput';
import {
  labelMonth,
  labelMonths,
  labelPasswordExpiration,
  labelDay,
  labelDays,
} from '../../../translatedLabels';
import { SecurityPolicy } from '../../../models';
import { getField } from '../../utils';
import TimeInputs from '../../../TimeInputs';

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
    setFieldValue(passwordExpirationFieldName, value || null);
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
      <TimeInputs
        baseName={passwordExpirationFieldName}
        timeValue={passwordExpirationValue}
        units={['months', 'days']}
        onChange={change}
      />
      {passwordExpirationError && (
        <FormHelperText error>{passwordExpirationError}</FormHelperText>
      )}
    </div>
  );
};

export default PasswordExpiration;
