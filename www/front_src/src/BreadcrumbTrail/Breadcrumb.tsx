import React from 'react';

import { Link as RouterLink } from 'react-router-dom';

import { Link, makeStyles } from '@material-ui/core';

import { Breadcrumb as BreadcrumbModel } from './models';

const useStyles = makeStyles(() => ({
  link: {
    '&:hover': {
      textDecoration: 'underline',
    },
    color: 'inherit',
    fontSize: 'small',
    textDecoration: 'none',
  },
}));

interface Props {
  breadcrumb: BreadcrumbModel;
  last: boolean;
}

const Breadcrumb = ({ last, breadcrumb }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Link
      className={classes.link}
      color={last ? 'textPrimary' : 'inherit'}
      component={RouterLink}
      to={breadcrumb.link}
    >
      {breadcrumb.label}
    </Link>
  );
};

export default Breadcrumb;
