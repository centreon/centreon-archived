import React from 'react';

import { Link as RouterLink } from 'react-router-dom';

import { Link, makeStyles } from '@material-ui/core';
import { BreadcrumbItem } from './models';

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
  count: number;
  breadcrumb: BreadcrumbItem;
}

const BreadcrumbsLink = ({ index, count, breadcrumb }: Props): JSX.Element => {
  const classes = useStyles();

  const isLastLink = index === count - 1;

  return (
    <Link
      className={classes.link}
      color={isLastLink ? 'textPrimary' : 'inherit'}
      component={RouterLink}
      to={breadcrumb.link}
    >
      {breadcrumb.label}
    </Link>
  );
};

export default BreadcrumbsLink;
