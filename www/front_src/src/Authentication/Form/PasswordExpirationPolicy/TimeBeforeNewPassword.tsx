import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';

import { FormHelperText, FormLabel } from '@material-ui/core';

import { labelTimeBeforeSetNewPassword } from '../../translatedLabels';
import { getField } from '../utils';
import TimeInputs from '../../TimeInputs';

const delayBeforeNewPasswordFieldName = 'delayBeforeNewPassword';

const TimeBeforeNewPassword = (): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue, errors } = useFormikContext<FormikValues>();

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
      <FormLabel>{t(labelTimeBeforeSetNewPassword)}</FormLabel>
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
