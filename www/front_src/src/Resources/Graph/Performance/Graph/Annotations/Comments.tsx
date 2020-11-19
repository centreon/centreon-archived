import * as React from 'react';

import { propEq, filter } from 'ramda';
import { useTranslation } from 'react-i18next';

import { useTheme } from '@material-ui/core';
import IconComment from '@material-ui/icons/Comment';

import { TimelineEvent } from '../../../../Details/tabs/Timeline/models';
import { labelBy } from '../../../../translatedLabels';
import truncate from '../../../../truncate';
import Annotation from './Annotation';
import { Props } from '.';

const CommentAnnotations = ({
  xScale,
  timeline,
  graphHeight,
}: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const comments = filter<TimelineEvent>(propEq('type', 'comment'), timeline);

  const iconSize = 20;

  return (
    <>
      {comments.map((comment) => {
        const content = `${truncate(comment.content)} ${t(labelBy)} ${
          comment.contact?.name
        }`;

        const icon = (
          <IconComment height={iconSize} width={iconSize} color="primary" />
        );

        return (
          <Annotation
            key={comment.id}
            icon={icon}
            content={content}
            date={comment.date}
            graphHeight={graphHeight}
            iconSize={iconSize}
            color={theme.palette.primary.main}
            xScale={xScale}
          />
        );
      })}
    </>
  );
};

export default CommentAnnotations;
