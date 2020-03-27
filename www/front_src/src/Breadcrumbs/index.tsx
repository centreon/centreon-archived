import React, { useMemo } from 'react';

import { connect } from 'react-redux';

import { makeStyles, Breadcrumbs as MuiBreadcrumbs } from '@material-ui/core';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';

import breadcrumbsSelector from './selector';
import { BreadcrumbItem } from './models';
import BreadcrumbsLink from './Link';

const useStyles = makeStyles({
  root: {
    padding: '4px 16px',
  },
  item: {
    display: 'flex',
  },
});

interface Props {
  breadcrumbs;
  path: string;
  children: React.ReactNode;
}

const getLinks = (breadcrumbs, path): Array<BreadcrumbItem> => {
  if (breadcrumbs[path]) {
    return breadcrumbs[path];
  }
  if (path.includes('/')) {
    const shorterPath = path
      .split('/')
      .slice(0, -1)
      .join('/');
    return getLinks(breadcrumbs, shorterPath);
  }

  return [];
};

const Breadcrumbs = ({ breadcrumbs, path }: Props): JSX.Element => {
  const classes = useStyles();

  const links = useMemo(() => getLinks(breadcrumbs, path), [
    breadcrumbs,
    getLinks,
    path,
  ]);

  return (
    <MuiBreadcrumbs
      classes={{ root: classes.root, li: classes.item }}
      separator={<NavigateNextIcon fontSize="small" />}
      aria-label="Breadcrumb"
    >
      {links &&
        links.map((link, index) => (
          <BreadcrumbsLink
            key={`${link.label}${link.index}`}
            breadcrumb={link}
            index={index}
            count={links.length}
          />
        ))}
    </MuiBreadcrumbs>
  );
};

const mapStateToProps = (state): { breadcrumbs } => ({
  breadcrumbs: breadcrumbsSelector(state),
});

export default connect(mapStateToProps)(Breadcrumbs);
