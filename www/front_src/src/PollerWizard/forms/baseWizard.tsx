import * as React from 'react';

import { makeStyles, Paper } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  page: {
    margin: '0 auto',
    padding: theme.spacing(2, 0),
    width: '40%',
  },
}));

interface Props {
  children: React.ReactNode;
}

const BaseWizard = ({ children }: Props): JSX.Element => {
  const classes = useStyles();

  return <Paper className={classes.page}>{children}</Paper>;
};

export default BaseWizard;
