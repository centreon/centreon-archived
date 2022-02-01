import React from 'react';

import { makeStyles } from '@mui/styles';

const useStyles = makeStyles((theme) => ({
  contentWrapper: {
    [theme.breakpoints.up(767)]: {
      padding: theme.spacing(1.5),
    },
    boxSizing: 'border-box',
    margin: theme.spacing(0, 'auto'),
    padding: theme.spacing(1.5, 2.5, 0, 2.5),
  },
}));

interface Props {
  children: React.ReactChildren;
}

const ExtensionsWrapper = ({ children }: Props): JSX.Element => {
  const classes = useStyles();

  return <div className={classes.contentWrapper}>{children}</div>;
};

export default ExtensionsWrapper;
