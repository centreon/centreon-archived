import * as React from 'react';

import { Typography, makeStyles } from '@material-ui/core';

import Card from '../Card';

const useStyles = makeStyles((theme) => ({
  active: {
    color: theme.palette.success.main,
  },
  container: {
    height: 65,
  },
  title: {
    display: 'flex',
    gridGap: theme.spacing(1),
  },
}));

interface Props {
  isCustomCard?: boolean;
  line: JSX.Element;
  title: string;
}

const DetailsCard = ({
  title,
  line,
  isCustomCard = false,
}: Props): JSX.Element => {
  const classes = useStyles();

  if (isCustomCard) {
    return line;
  }

  return (
    <Card className={classes.container}>
      <div className={classes.title}>
        <Typography gutterBottom color="textSecondary" variant="body1">
          {title}
        </Typography>
      </div>
      {line}
    </Card>
  );
};

export default DetailsCard;
