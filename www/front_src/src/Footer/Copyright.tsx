import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';

const useStyles = makeStyles({
  copyright: {
    alignItems: 'center',
    display: 'flex',
  },
});

const Copyright = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Typography className={classes.copyright} variant="body2">
      Copyright © 2005 - 2021
    </Typography>
  );
};

export default Copyright;
