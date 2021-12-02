import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';

import { makeStyles, Typography } from '@material-ui/core';

import { TextField, useMemoComponent } from '@centreon/ui';

import {
  labelPasswordCasePolicy,
  labelPasswordLength,
} from '../../translatedLabels';
import { getField } from '../utils';

import CaseButtons from './CaseButtons';

const passwordMinLengthFieldName = 'passwordMinLength';

const useStyles = makeStyles((theme) => ({
  fields: {
    alignItems: 'top',
    columnGap: theme.spacing(1),
    display: 'grid',
    gridTemplateColumns: '0.9fr 1fr',
    marginTop: theme.spacing(1),
    width: theme.spacing(60),
  },
}));

const PasswordCasePolicy = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { handleChange, errors, values } = useFormikContext<FormikValues>();

  const passwordLengthError = getField<string | undefined>({
    field: passwordMinLengthFieldName,
    object: errors,
  });

  const passwordLengthValue = getField<number>({
    field: passwordMinLengthFieldName,
    object: values,
  });

  return useMemoComponent({
    Component: (
      <div>
        <Typography variant="h5">{t(labelPasswordCasePolicy)}</Typography>
        <div className={classes.fields}>
          <TextField
            fullWidth
            required
            error={passwordLengthError}
            helperText={passwordLengthError}
            inputProps={{
              'aria-label': t(labelPasswordLength),
              min: 0,
            }}
            label={t(labelPasswordLength)}
            name={passwordMinLengthFieldName}
            type="number"
            value={passwordLengthValue}
            onChange={handleChange(passwordMinLengthFieldName)}
          />
          <CaseButtons />
        </div>
      </div>
    ),
    memoProps: [passwordLengthError, passwordLengthValue],
  });
};

export default PasswordCasePolicy;
