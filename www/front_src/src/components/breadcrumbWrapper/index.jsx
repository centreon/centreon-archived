import React, { useMemo, useCallback } from 'react';
import { connect } from 'react-redux';
import { Breadcrumb } from '@centreon/ui';
import breadcrumbsSelector from '../../redux/selectors/navigation/breadcrumbs';

function BreadcrumbWrapper({ breadcrumbs, path, children, ...others }) {
  const getBreadcrumbPath = useCallback((breadcrumbs, path) => {
    if (breadcrumbs[path]) {
      return breadcrumbs[path];
    }
    if (path.includes('/')) {
      const shorterPath = path
        .split('/')
        .slice(0, -1)
        .join('/');
      return getBreadcrumbPath(breadcrumbs, shorterPath);
    }

    return [];
  });

  const breadcrumbPath = useMemo(() => getBreadcrumbPath(breadcrumbs, path), [
    breadcrumbs,
    getBreadcrumbPath,
    path,
  ]);

  return (
    <>
      <Breadcrumb breadcrumbs={breadcrumbPath} {...others} />
      {children}
    </>
  );
}

const mapStateToProps = (state) => ({
  breadcrumbs: breadcrumbsSelector(state),
});

export default connect(mapStateToProps)(BreadcrumbWrapper);
