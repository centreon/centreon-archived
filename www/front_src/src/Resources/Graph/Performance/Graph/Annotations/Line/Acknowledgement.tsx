import * as React from 'react';

import { useTheme } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { Props } from '..';
import EventAnnotations from '../EventAnnotations';
import { iconSize } from '../Annotation';

const AcknowledgementAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();

  const color = theme.palette.action.acknowledged;

  const icon = (
    <IconAcknowledge
      height={iconSize}
      width={iconSize}
      style={{
        color,
      }}
    />
  );

  return (
    <EventAnnotations
      type="acknowledgement"
      icon={icon}
      color={color}
      {...props}
    />
  );
};

export default AcknowledgementAnnotations;
