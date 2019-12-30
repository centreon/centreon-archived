import React, { useMemo, useCallback, ReactNode } from 'react';
import { connect } from 'react-redux';
import Breadcrumb from '@centreon/ui/Breadcrumb';
import breadcrumbsSelector from '../../redux/selectors/navigation/breadcrumbs';

interface Props {
  breadcrumbs: object;
  path: string;
  children: ReactNode;
  others: object;
}

function BreadcrumbWrapper({ breadcrumbs, path, children, ...others }: Props) {
  const getBreadcrumbPath = useCallback(() => {
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
  }, [breadcrumbs, path]);

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
