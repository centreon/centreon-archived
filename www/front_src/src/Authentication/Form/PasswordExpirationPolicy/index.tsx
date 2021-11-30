import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography } from '@material-ui/core';

import { labelPasswordExpirationPolicy } from '../../translatedLabels';

import PasswordExpiration from './PasswordExpiration';

const PasswordExpirationPolicy = (): JSX.Element => {
  const { t } = useTranslation();

  return (
    <div>
      <Typography variant="h6">{t(labelPasswordExpirationPolicy)}</Typography>
      <PasswordExpiration />
    </div>
  );
};

export default PasswordExpirationPolicy;
