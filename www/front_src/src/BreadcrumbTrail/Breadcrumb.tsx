import React from 'react';

import { Link as RouterLink } from 'react-router-dom';

import { Link } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { Breadcrumb as BreadcrumbModel } from './models';

const useStyles = makeStyles(() => ({
  link: {
    // eslint-disable-next-line @typescript-eslint/naming-convention
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
