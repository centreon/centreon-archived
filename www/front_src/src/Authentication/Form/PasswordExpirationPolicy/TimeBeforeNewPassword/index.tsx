import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext } from 'formik';

import { FormHelperText, Typography } from '@material-ui/core';

import { labelTimeBeforeSetNewPassword } from '../../../translatedLabels';
import { getField } from '../../utils';
import TimeInputs from '../../../TimeInputs';
import { SecurityPolicy } from '../../../models';

const delayBeforeNewPasswordFieldName = 'delayBeforeNewPassword';

const TimeBeforeNewPassword = (): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue, errors } = useFormikContext<SecurityPolicy>();

  const change = (value: number): void => {
    setFieldValue(delayBeforeNewPasswordFieldName, value || null);
  };

  const delayBeforeNewPasswordValue = React.useMemo<number>(
    () => getField({ field: delayBeforeNewPasswordFieldName, object: values }),
    [values],
  );

  const delayBeforeNewPasswordError = React.useMemo<number>(
    () => getField({ field: delayBeforeNewPasswordFieldName, object: errors }),
    [errors],
  );

  return (
    <div>
      <Typography variant="h6">{t(labelTimeBeforeSetNewPassword)}</Typography>
      <TimeInputs
        baseName={delayBeforeNewPasswordFieldName}
        timeValue={delayBeforeNewPasswordValue}
        units={['days', 'hours', 'minutes', 'seconds']}
        onChange={change}
      />
      {delayBeforeNewPasswordError && (
        <FormHelperText error>{delayBeforeNewPasswordError}</FormHelperText>
      )}
    </div>
  );
};

export default TimeBeforeNewPassword;
