import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext } from 'formik';

import { FormHelperText, FormLabel, makeStyles } from '@material-ui/core';

import { labelPasswordExpiration } from '../../../translatedLabels';
import { SecurityPolicy } from '../../../models';
import { getField } from '../../utils';
import TimeInputs from '../../../TimeInputs';

const passwordExpirationFieldName = 'passwordExpiration';

const useStyles = makeStyles((theme) => ({
  passwordExpirationContainer: {
    marginTop: theme.spacing(1),
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
      <FormLabel>{t(labelPasswordExpiration)}</FormLabel>
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
