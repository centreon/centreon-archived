import * as React from 'react';

import { useTheme } from '@material-ui/core';
import IconDowntime from '../../../../../icons/Downtime';

import { Props } from '..';
import EventAnnotations from '../EventAnnotations';
import { iconSize } from '../Annotation';

const DowntimeAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();

  const color = theme.palette.action.inDowntime;

  const icon = (
    <IconDowntime height={iconSize} width={iconSize} style={{ color }} />
  );

  return (
    <EventAnnotations type="downtime" icon={icon} color={color} {...props} />
  );
};

export default DowntimeAnnotations;
