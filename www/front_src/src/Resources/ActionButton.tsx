import * as React from 'react';

import { makeStyles, IconButton } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  button: {
    padding: theme.spacing(0.25),
  },
}));

const ActionButton = (props): JSX.Element => {
  const classes = useStyles();

  return <IconButton className={classes.button} color="primary" {...props} />;
};

export default ActionButton;
