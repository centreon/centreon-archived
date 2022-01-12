import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@mui/material';

import { labelDowntime } from '../../../../../translatedLabels';
import IconDowntime from '../../../../../icons/Downtime';
import { Props } from '..';
import EventAnnotations from '../EventAnnotations';

const DowntimeAnnotations = (props: Props): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

  const color = theme.palette.action.inDowntime;

  return (
    <EventAnnotations
      Icon={IconDowntime}
      ariaLabel={t(labelDowntime)}
      color={color}
      type="downtime"
      {...props}
    />
  );
};

export default DowntimeAnnotations;
