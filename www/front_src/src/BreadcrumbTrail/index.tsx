import React, { useMemo } from 'react';

import { connect } from 'react-redux';

import { makeStyles, Breadcrumbs as MuiBreadcrumbs } from '@material-ui/core';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';

import breadcrumbSelector from './selector';
import { Breadcrumb as BreadcrumbModel, BreadcrumbsByPath } from './models';
import Breadcrumb from './Breadcrumb';

const useStyles = makeStyles({
  root: {
    padding: '4px 16px',
  },
  item: {
    display: 'flex',
  },
});

interface Props {
  breadcrumbsByPath: BreadcrumbsByPath;
  path: string;
  children: React.ReactNode;
}

const getBreadcrumbs = ({
  breadcrumbsByPath,
  path,
}): Array<BreadcrumbModel> => {
  if (breadcrumbsByPath[path]) {
    return breadcrumbsByPath[path];
  }

  if (path.includes('/')) {
    const shorterPath = path
      .split('/')
      .slice(0, -1)
      .join('/');

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
      classes={{ root: classes.root, li: classes.item }}
      separator={<NavigateNextIcon fontSize="small" />}
      aria-label="Breadcrumb"
    >
      {breadcrumbs.map((breadcrumb, index) => (
        <Breadcrumb
          key={`${breadcrumb.label}-${breadcrumb.index}`}
          breadcrumb={breadcrumb}
          index={index}
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
