import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

import { labelComment } from '../../../../../translatedLabels';
import { Props } from '..';
import EventAnnotations from '../EventAnnotations';

const CommentAnnotations = (props: Props): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

  return (
    <EventAnnotations
      type="comment"
      Icon={IconComment}
      color={theme.palette.primary.main}
      ariaLabel={t(labelComment)}
      {...props}
    />
  );
};

export default CommentAnnotations;
