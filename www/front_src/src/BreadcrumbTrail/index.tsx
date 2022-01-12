import React, { useMemo } from 'react';

import { connect } from 'react-redux';

import { Breadcrumbs as MuiBreadcrumbs } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import NavigateNextIcon from '@mui/icons-material/NavigateNext';

import breadcrumbSelector from './selector';
import { Breadcrumb as BreadcrumbModel, BreadcrumbsByPath } from './models';
import Breadcrumb from './Breadcrumb';

const useStyles = makeStyles({
  item: {
    display: 'flex',
  },
  root: {
    padding: '4px 16px',
  },
});

interface Props {
  breadcrumbsByPath: BreadcrumbsByPath;
  path: string;
}

const getBreadcrumbs = ({
  breadcrumbsByPath,
  path,
}): Array<BreadcrumbModel> => {
  if (breadcrumbsByPath[path]) {
    return breadcrumbsByPath[path];
  }

  if (path.includes('/')) {
    const shorterPath = path.split('/').slice(0, -1).join('/');

    return getBreadcrumbs({ breadcrumbsByPath, path: shorterPath });
  }

  return [];
};

const BreadcrumbTrail = ({ breadcrumbsByPath, path }: Props): JSX.Element => {
  const classes = useStyles();

  const breadcrumbs = useMemo(
    () => getBreadcrumbs({ breadcrumbsByPath, path }),
    [breadcrumbsByPath, path],
  );

  return (
    <MuiBreadcrumbs
      aria-label="Breadcrumb"
      classes={{ li: classes.item, root: classes.root }}
      separator={<NavigateNextIcon fontSize="small" />}
    >
      {breadcrumbs.map((breadcrumb, index) => (
        <Breadcrumb
          breadcrumb={breadcrumb}
          key={breadcrumb.label}
          last={index === breadcrumbs.length - 1}
        />
      ))}
    </MuiBreadcrumbs>
  );
};

const mapStateToProps = (state): { breadcrumbsByPath: BreadcrumbsByPath } => ({
  breadcrumbsByPath: breadcrumbSelector(state),
});

export default connect(mapStateToProps)(BreadcrumbTrail);
