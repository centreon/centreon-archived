import React, { useMemo } from 'react';
import { connect } from "react-redux";
import { breadcrumbsSelector } from '../../redux/selectors/navigation';
import { Breadcrumb } from '@centreon/react-components';

function BreadcrumbWrapper({ breadcrumbs, path, children }) {

  const getBreadcrumbPath = (breadcrumbs, path) => {
    let formattedBreadcrumbs = [];

    if (breadcrumbs[path]) {
      formattedBreadcrumbs = breadcrumbs[path].reduce((acc, breadcrumb) => {
        acc.push({
          label: breadcrumb[0],
          link: breadcrumb[1],
        });
        return acc;
      }, []);
    } else if (path.includes('/')) {
      const shorterPath = path.split('/').slice(0, -1).join('/');
      return getBreadcrumbPath(breadcrumbs, shorterPath);
    }

    return formattedBreadcrumbs;
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
