import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';

import { FormHelperText, FormLabel, makeStyles } from '@material-ui/core';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import { labelPasswordExpiration } from '../../translatedLabels';
import { getField } from '../utils';
import TimeInputs from '../../TimeInputs';

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

  return useMemoComponent({
    Component: (
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
    ),
    memoProps: [passwordExpirationValue, passwordExpirationError],
  });
};

export default PasswordExpiration;
