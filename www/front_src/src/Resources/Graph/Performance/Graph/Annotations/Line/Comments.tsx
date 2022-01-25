import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { useTheme } from '@mui/material';
import IconComment from '@mui/icons-material/Comment';

import { labelComment } from '../../../../../translatedLabels';
import { Props } from '..';
import EventAnnotations from '../EventAnnotations';

const CommentAnnotations = (props: Props): JSX.Element => {
  const { t } = useTranslation();
  const theme = useTheme();

  return (
    <EventAnnotations
      Icon={IconComment}
      ariaLabel={t(labelComment)}
      color={theme.palette.primary.main}
      type="comment"
      {...props}
    />
  );
};

export default CommentAnnotations;
