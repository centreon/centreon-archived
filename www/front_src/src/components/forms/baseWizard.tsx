import * as React from 'react';

import { makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  page: {
    // backgroundColor: theme.palette.common.white,
    padding: theme.spacing(2, 0),
  },
}));

interface Props {
  children: React.ReactNode;
}

const BaseWizard = ({ children }: Props): JSX.Element => {
  const classes = useStyles();

  return <div className={classes.page}>{children}</div>;
};

export default BaseWizard;
