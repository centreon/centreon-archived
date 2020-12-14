import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

import { labelComment } from '../../../../../translatedLabels';
import { Props } from '..';
import EventAnnotations from '../EventAnnotations';
import { iconSize } from '../Annotation';

const CommentAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

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
      color={theme.palette.primary.main}
      {...props}
    />
  );
};

export default CommentAnnotations;
