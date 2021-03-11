import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Card, CardContent, Typography, makeStyles } from '@material-ui/core';

import { labelComment } from '../../../translatedLabels';

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'grid',
    gridTemplateColumns: '1fr 2fr auto',
    gridTemplateAreas: ` 
      'content-title content chip'
      'comment-title comment chip'
      `,
    gridGap: theme.spacing(2),
  },
  contentTitle: {
    gridArea: 'content-title',
  },
  content: {
    gridArea: 'content',
  },
  chip: {
    gridArea: 'chip',
  },
  commentTitle: {
    gridArea: 'comment-title',
  },
  comment: {
    gridArea: 'comment',
  },
}));

interface Props {
  title: string;
  contentLines: Array<string>;
  chip: JSX.Element;
  commentLine: string;
}

const Line = (line): JSX.Element => (
  <Typography key={line} variant="body2" component="p">
    {line}
  </Typography>
);

const StateCard = ({
  title,
  contentLines,
  commentLine,
  chip,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <Card style={{ padding: 8 }}>
      <div className={classes.container}>
        <Typography
          className={classes.contentTitle}
          variant="subtitle2"
          color="textSecondary"
        >
          {title}
        </Typography>
        <div className={classes.content}>{contentLines.map(Line)}</div>

        <Typography
          className={classes.commentTitle}
          variant="subtitle2"
          color="textSecondary"
        >
          {t(labelComment)}
        </Typography>
        <div className={classes.comment}>{Line(commentLine)}</div>
        <div className={classes.chip}>{chip}</div>
      </div>
    </Card>
  );
};

export default StateCard;
