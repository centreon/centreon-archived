import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { labelAcknowledgement } from '../../../../../translatedLabels';
import { Props } from '..';
import EventAnnotations from '../EventAnnotations';
import { iconSize } from '../Annotation';

const AcknowledgementAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const color = theme.palette.action.acknowledged;

  const icon = (
    <IconAcknowledge
      aria-label={t(labelAcknowledgement)}
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
