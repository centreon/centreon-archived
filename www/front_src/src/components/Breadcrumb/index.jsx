import React from 'react';

import PropTypes from 'prop-types';

import { makeStyles } from '@material-ui/core/styles';
import Breadcrumbs from '@material-ui/core/Breadcrumbs';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';

import BreadcrumbLink from './Link';

const useStyles = makeStyles({
  breadcrumb: {
    padding: '4px 16px',
  },
  item: {
    display: 'flex',
  },
});

const Breadcrumb = ({ breadcrumbs }) => {
  const classes = useStyles();

  return (
    <Breadcrumbs
      classes={{ root: classes.breadcrumb, li: classes.item }}
      separator={<NavigateNextIcon fontSize="small" />}
      aria-label="Breadcrumb"
    >
      {breadcrumbs &&
        breadcrumbs.map((breadcrumb, index) => (
          <BreadcrumbLink
            key={`${breadcrumb.label}${breadcrumb.index}`}
            breadcrumb={breadcrumb}
            index={index}
            count={breadcrumbs.length}
          />
        ))}
    </Breadcrumbs>
  );
};

Breadcrumb.propTypes = {
  breadcrumbs: PropTypes.arrayOf(PropTypes.shape).isRequired,
};

export default Breadcrumb;
