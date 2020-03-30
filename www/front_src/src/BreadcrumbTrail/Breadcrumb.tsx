import React from 'react';

import { Link as RouterLink } from 'react-router-dom';

import { Link, makeStyles } from '@material-ui/core';
import { Breadcrumb as BreadcrumbModel } from './models';

const useStyles = makeStyles(() => ({
  link: {
    fontSize: 'small',
    color: 'inherit',
    textDecoration: 'none',
    '&:hover': {
      textDecoration: 'underline',
    },
  },
}));

interface Props {
  index: number;
  last: boolean;
  breadcrumb: BreadcrumbModel;
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
