import { lazy, Suspense } from 'react';

import { Routes, Route, useHref } from 'react-router-dom';
import { isNil, not, propOr } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { styled } from '@mui/material';

import { PageSkeleton, useMemoComponent } from '@centreon/ui';

import internalPagesRoutes from '../../reactRoutes';
import { dynamicImport } from '../../helpers/dynamicImport';
import BreadcrumbTrail from '../../BreadcrumbTrail';
import useNavigation from '../../Navigation/useNavigation';
import { federatedComponentsAtom } from '../../federatedComponents/atoms';

const NotAllowedPage = lazy(() => import('../../FallbackPages/NotAllowedPage'));
const NotFoundPage = lazy(() => import('../../FallbackPages/NotFoundPage'));

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
    const Page = lazy(() => dynamicImport(basename, parameter));

    return (
      <Route
        element={
          <PageContainer>
            <BreadcrumbTrail path={path} />
            <Suspense
              fallback={<PageSkeleton displayHeaderAndNavigation={false} />}
            >
              <Page />
            </Suspense>
          </PageContainer>
        }
        key={path}
        path={path}
      />
    );
  });
};

interface Props {
  allowedPages: Array<string | Array<string>>;
  externalPagesFetched: boolean;
  pages: Record<string, unknown> | null;
}

const ReactRouterContent = ({
  pages,
  externalPagesFetched,
  allowedPages,
}: Props): JSX.Element => {
  const basename = useHref('/');

  return useMemoComponent({
    Component: (
      <Suspense fallback={<PageSkeleton />}>
        <Routes>
          {internalPagesRoutes.map(({ path, comp: Comp, ...rest }) => (
            <Route
              element={
                allowedPages.includes(path) ? (
                  <PageContainer>
                    <BreadcrumbTrail path={path} />
                    <Comp />
                  </PageContainer>
                ) : (
                  <NotAllowedPage />
                )
              }
              key={path}
              path={path}
              {...rest}
            />
          ))}
          {/* getExternalPageRoutes({ allowedPages, basename, pages }) */}
          {externalPagesFetched && (
            <Route element={<NotFoundPage />} path="*" />
          )}
        </Routes>
      </Suspense>
    ),
    memoProps: [externalPagesFetched, pages, allowedPages],
  });
};

const ReactRouter = (): JSX.Element => {
  const { allowedPages } = useNavigation();

  if (!allowedPages) {
    return <PageSkeleton />;
  }

  return (
    <ReactRouterContent
      externalPagesFetched
      allowedPages={allowedPages}
      pages={null}
    />
  );
};

export default ReactRouter;
