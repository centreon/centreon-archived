import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@material-ui/core';
import IconAcknowledge from '@material-ui/icons/Person';

import { TimelineEvent } from '../../../../../Details/tabs/Timeline/models';
import { labelBy, labelAcknowledgement } from '../../../../../translatedLabels';
import truncate from '../../../../../truncate';
import { Props } from '..';

import EventAnnotations from '.';

const AcknowledgementAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const iconSize = 20;

  const getContent = (event: TimelineEvent): string => {
    return `${truncate(event.content)} (${t(labelBy)} ${event.contact?.name})`;
  };

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
      getContent={getContent}
      iconSize={iconSize}
      color={color}
      {...props}
    />
  );
};

export default AcknowledgementAnnotations;
