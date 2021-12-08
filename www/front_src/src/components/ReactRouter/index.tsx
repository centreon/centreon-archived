import * as React from 'react';

import { connect } from 'react-redux';
import { Routes, Route, useHref } from 'react-router-dom';
import { equals } from 'ramda';

import { styled } from '@material-ui/core';

import { PageSkeleton } from '@centreon/ui';

import internalPagesRoutes from '../../route-maps';
import { dynamicImport } from '../../helpers/dynamicImport';
import NotAllowedPage from '../../route-components/notAllowedPage';
import BreadcrumbTrail from '../../BreadcrumbTrail';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import useNavigation from '../../Navigation/useNavigation';

const PageContainer = styled('div')(({ theme }) => ({
  background: theme.palette.background.default,
  display: 'grid',
  gridTemplateRows: 'auto 1fr',
  height: '100%',
  overflow: 'auto',
}));

const getExternalPageRoutes = ({
  allowedPages,
  pages,
  basename,
}): Array<JSX.Element> => {
  const pageEntries = Object.entries(pages);
  const isAllowedPage = (path): boolean =>
    allowedPages?.find((allowedPage) => path.includes(allowedPage));

  const loadablePages = pageEntries.filter(([path]) => isAllowedPage(path));

  return loadablePages.map(([path, parameter]) => {
    const Page = React.lazy(() => dynamicImport(basename, parameter));

    return (
      <Route
        element={
          <PageContainer>
            <BreadcrumbTrail path={path} />
            <Page />
          </PageContainer>
        }
        key={path}
        path={path}
      />
    );
  });
};

interface Props {
  externalPagesFetched: boolean;
  pages: Record<string, unknown>;
}

const ReactRouter = React.memo<Props>(
  ({ pages, externalPagesFetched }: Props) => {
    const { allowedPages } = useNavigation();
    const basename = useHref('/');
    if (!externalPagesFetched || !allowedPages) {
      return <PageSkeleton />;
    }

    return (
      <React.Suspense fallback={<PageSkeleton />}>
        <Routes>
          {internalPagesRoutes.map(({ path, comp: Comp, ...rest }) => (
            <Route
              element={
                <PageContainer>
                  {allowedPages.includes(path) ? (
                    <>
                      <BreadcrumbTrail path={path} />
                      <Comp />
                    </>
                  ) : (
                    <NotAllowedPage />
                  )}
                </PageContainer>
              }
              key={path}
              path={path}
              {...rest}
            />
          ))}
          {getExternalPageRoutes({ allowedPages, basename, pages })}
          {externalPagesFetched && <Route element={<NotAllowedPage />} />}
        </Routes>
      </React.Suspense>
    );
  },
  (previousProps, nextProps) =>
    equals(previousProps.pages, nextProps.pages) &&
    equals(previousProps.externalPagesFetched, nextProps.externalPagesFetched),
);

const mapStateToProps = (state): Props => ({
  externalPagesFetched: state.externalComponents.fetched,
  pages: state.externalComponents.pages,
});

export default connect(mapStateToProps)(ReactRouter);
