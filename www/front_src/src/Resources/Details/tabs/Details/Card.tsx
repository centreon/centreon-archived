import { ReactNode } from 'react';

import { makeStyles } from 'tss-react/mui';

import { Paper } from '@mui/material';

const useStyles = makeStyles()((theme) => ({
  content: {
    padding: theme.spacing(1, 2, 2, 2),
  },
}));

interface Props {
  children?: ReactNode;
  className?: string;
}

const Card = ({ children, className }: Props): JSX.Element => {
  const { classes } = useStyles();

  return (
    <Paper className={className} elevation={0}>
      <div className={classes.content}>{children}</div>
    </Paper>
  );
};

export default Card;
