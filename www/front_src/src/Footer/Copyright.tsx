import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';

import { useUserContext } from '@centreon/ui-context';

const useStyles = makeStyles({
  copyright: {
    alignItems: 'center',
    display: 'flex',
  },
});

const Copyright = (): JSX.Element => {
  const classes = useStyles();
  const { timezone } = useUserContext();

  const now = new Date();

  const year = now.toLocaleDateString('fr', {
    timeZone: timezone,
    year: 'numeric',
  });

  const label = `Copyright © 2005 - ${year}`;

  return (
    <Typography className={classes.copyright} variant="body2">
      {label}
    </Typography>
  );
};

export default Copyright;
