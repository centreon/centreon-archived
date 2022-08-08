import { useTranslation } from 'react-i18next';

import { Card, Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelComment } from '../../../translatedLabels';

const useStyles = makeStyles((theme) => ({
  chip: {
    gridArea: 'chip',
  },
  comment: {
    gridArea: 'comment',
  },
  commentTitle: {
    gridArea: 'comment-title',
  },
  container: {
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateAreas: ` 
      'content-title content chip'
      'comment-title comment chip'
      `,
    gridTemplateColumns: '1fr 2fr auto',
  },
  content: {
    gridArea: 'content',
  },
  contentTitle: {
    gridArea: 'content-title',
  },
}));

interface Props {
  chip: JSX.Element;
  commentLine: string;
  contentLines: Array<string>;
  title: string;
}

const Line = (line): JSX.Element => (
  <Typography component="p" key={line} variant="body2">
    {line}
  </Typography>
);

const StateCard = ({
  title,
  contentLines,
  commentLine,
  chip,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <Card elevation={0} style={{ padding: 8 }}>
      <div className={classes.container}>
        <Typography
          className={classes.contentTitle}
          color="textSecondary"
          variant="subtitle2"
        >
          {title}
        </Typography>
        <div className={classes.content}>{contentLines.map(Line)}</div>

        <Typography
          className={classes.commentTitle}
          color="textSecondary"
          variant="subtitle2"
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
