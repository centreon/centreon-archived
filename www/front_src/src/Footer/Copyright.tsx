import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/ui';

const useStyles = makeStyles({
  copyright: {
    alignItems: 'center',
    display: 'flex',
  },
});

const Copyright = (): JSX.Element => {
  const classes = useStyles();
  const { format } = useLocaleDateTimeFormat();

  const year = format({
    date: new Date(),
    formatString: 'YYYY',
  });

  return (
    <Typography className={classes.copyright} variant="body2">
      Copyright © 2005 - {year}
    </Typography>
  );
};

export default Copyright;
