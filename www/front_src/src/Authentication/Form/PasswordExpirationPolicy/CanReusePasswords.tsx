import * as React from 'react';

import { useFormikContext } from 'formik';
import { useTranslation } from 'react-i18next';

import { FormControlLabel, makeStyles, Switch } from '@material-ui/core';

import { SecurityPolicy } from '../../models';
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
  const { values, handleChange } = useFormikContext<SecurityPolicy>();
  const { t } = useTranslation();

  const canReusePasswords = React.useMemo(
    () => getField<boolean>({ field: fieldName, object: values }),
    [values],
  );

  return (
    <div className={classes.canReusePasswords}>
      <FormControlLabel
        control={
          <Switch
            aria-label={t(labelCanReuseLast3Passwords)}
            checked={canReusePasswords}
            color="primary"
            name={t(labelCanReuseLast3Passwords)}
            size="small"
            onChange={handleChange(fieldName)}
          />
        }
        label={t(labelCanReuseLast3Passwords)}
      />
    </div>
  );
};

export default CanReusePasswords;
