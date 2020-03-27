import React, { useMemo } from 'react';

import { connect } from 'react-redux';

import Breadcrumb from './Breadcrumb';
import breadcrumbsSelector from './selector';

interface Props {
  breadcrumbs;
  path: string;
  children: React.ReactNode;
}

const getLinks = (breadcrumbs, path): Array<string> => {
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

const MemoizedBreadcrumb = ({
  breadcrumbs,
  path,
  children,
  ...others
}: Props): JSX.Element => {
  const links = useMemo(() => getLinks(breadcrumbs, path), [
    breadcrumbs,
    getLinks,
    path,
  ]);

  return (
    <>
      <Breadcrumb links={links} {...others} />
      {children}
    </>
  );
};

const mapStateToProps = (state): { breadcrumbs } => ({
  breadcrumbs: breadcrumbsSelector(state),
});

export default connect(mapStateToProps)(MemoizedBreadcrumb);
