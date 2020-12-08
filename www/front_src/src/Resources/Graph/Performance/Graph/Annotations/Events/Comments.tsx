import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

import { TimelineEvent } from '../../../../../Details/tabs/Timeline/models';
import { labelBy, labelComment } from '../../../../../translatedLabels';
import truncate from '../../../../../truncate';
import { Props } from '..';

import EventAnnotations from '.';

const CommentAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const iconSize = 20;

  const getContent = (event: TimelineEvent): string => {
    return `${truncate(event.content)} ${t(labelBy)} ${event.contact?.name}`;
  };

  const icon = (
    <IconComment
      aria-label={t(labelComment)}
      height={iconSize}
      width={iconSize}
      color="primary"
    />
  );

  return (
    <EventAnnotations
      type="comment"
      icon={icon}
      getContent={getContent}
      iconSize={iconSize}
      color={theme.palette.primary.main}
      {...props}
    />
  );
};

export default CommentAnnotations;
