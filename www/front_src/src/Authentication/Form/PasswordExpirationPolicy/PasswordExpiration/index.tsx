import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useFormikContext } from 'formik';

import { Typography } from '@material-ui/core';

import TimeInput from '../../../TimeInput';
import {
  labelPasswordExpiration,
  labelPasswordExpirationPolicy,
  labelWeek,
  labelWeeks,
} from '../../../translatedLabels';
import { SecurityPolicy } from '../../../models';
import { getField } from '../../utils';

const passwordExpirationFieldName = 'passwordExpiration';

const PasswordExpiration = (): JSX.Element => {
  const { t } = useTranslation();

  const { values, setFieldValue } = useFormikContext<SecurityPolicy>();

  const change = (value: number): void => {
    setFieldValue(passwordExpirationFieldName, value);
  };

  const passwordExpirationValue = React.useMemo<number>(
    () => getField({ field: passwordExpirationFieldName, object: values }),
    [values],
  );

  console.log(passwordExpirationValue);

  return (
    <div>
      <Typography>{t(labelPasswordExpiration)}</Typography>
      <TimeInput
        labels={{
          plural: labelWeeks,
          singular: labelWeek,
        }}
        name={passwordExpirationFieldName}
        timeValue={passwordExpirationValue}
        unit="weeks"
        onChange={change}
      />
    </div>
  );
};

export default PasswordExpiration;
