import * as React from 'react';

import { makeStyles } from '@material-ui/core';

const useStyles = makeStyles(() => ({
  page: {
    backgroundColor: '#FFF',
    padding: '15px 0px 15px 0px',
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
