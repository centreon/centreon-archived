import * as React from 'react';

import { FormikValues, useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';

import { FormControlLabel, Switch } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import { labelCanReuseLast3Passwords } from '../../translatedLabels';
import { getField } from '../utils';

const fieldName = 'canReusePasswords';

const useStyles = makeStyles((theme) => ({
  canReusePasswords: {
    marginLeft: theme.spacing(0.5),
  },
}));

const CanReusePasswords = (): JSX.Element => {
  const classes = useStyles();
  const { values, handleChange } = useFormikContext<FormikValues>();
  const { t } = useTranslation();

  const canReusePasswords = getField<boolean>({
    field: fieldName,
    object: values,
  });

  return useMemoComponent({
    Component: (
      <div className={classes.canReusePasswords}>
        <FormControlLabel
          control={
            <Switch
              checked={canReusePasswords}
              color="primary"
              name={t(labelCanReuseLast3Passwords)}
              size="small"
              onChange={handleChange(fieldName)}
            />
          }
          label={t(labelCanReuseLast3Passwords) as string}
        />
      </div>
    ),
    memoProps: [canReusePasswords],
  });
};

export default CanReusePasswords;
