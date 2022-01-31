import React from 'react';

import { makeStyles } from '@mui/styles';

const useStyles = makeStyles({
  contentWrapper: {
    '@media (min-width: 767px)': {
      padding: '12px',
    },
    boxSizing: 'border-box',
    margin: '0 auto',

    padding: '12px 20px 0 20px',
  },
});

interface Props {
  children: React.ReactChildren;
}

const ExtensionsWrapper = ({ children }: Props): JSX.Element => {
  const classes = useStyles();

  return <div className={classes.contentWrapper}>{children}</div>;
};

export default ExtensionsWrapper;
