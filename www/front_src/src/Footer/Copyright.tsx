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

  const label = `Copyright © 2005 - ${year}`;

  return (
    <Typography className={classes.copyright} variant="body2">
      {label}
    </Typography>
  );
};

export default Copyright;
