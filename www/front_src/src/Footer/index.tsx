import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import Copyright from './Copyright';
import Links from './Links';

const useStyles = makeStyles((theme) => ({
  footerContainer: {
    backgroundColor: '#232f39',
    color: theme.palette.common.white,
    display: 'grid',
    gridTemplateColumns: '7fr 1fr',
    height: theme.spacing(4),
    width: '100%',
  },
}));

const Footer = (): JSX.Element => {
  const classes = useStyles();

  return (
    <footer className={classes.footerContainer}>
      <Links />
      <Copyright />
    </footer>
  );
};

export default Footer;
