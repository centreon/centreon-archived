import * as React from 'react';

import { useTheme } from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

import { Props } from '..';
import EventAnnotations from '../EventAnnotations';
import { iconSize } from '../Annotation';

const CommentAnnotations = (props: Props): JSX.Element => {
  const theme = useTheme();

  const icon = (
    <IconComment height={iconSize} width={iconSize} color="primary" />
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
