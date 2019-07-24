import React, { useMemo } from 'react';
import { connect } from "react-redux";
import { breadcrumbsSelector } from '../../redux/selectors/navigation';
import { Breadcrumb } from '@centreon/react-components';

function BreadcrumbWrapper({ breadcrumbs, path, children }) {

  const getBreadcrumbPath = (breadcrumbs, path) => {
    if (breadcrumbs[path]) {
      return breadcrumbs[path];
    } else if (path.includes('/')) {
      const shorterPath = path.split('/').slice(0, -1).join('/');
      return getBreadcrumbPath(breadcrumbs, shorterPath);
    }

    return [];
  };

  const breadcrumbPath = useMemo(
    () => getBreadcrumbPath(breadcrumbs, path),
    [breadcrumbs, path]
  );

  return (
    <>
      <Breadcrumb breadcrumbs={breadcrumbPath}/>
      {children}
    </>
  );
}

const mapStateToProps = (state) => ({
  breadcrumbs: breadcrumbsSelector(state),
});

export default connect(mapStateToProps)(BreadcrumbWrapper);
