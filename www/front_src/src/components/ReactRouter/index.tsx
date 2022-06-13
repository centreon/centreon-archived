import { lazy, Suspense } from 'react';

import { Routes, Route } from 'react-router-dom';
import { isNil, not } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { styled } from '@mui/material';

import { PageSkeleton, useMemoComponent } from '@centreon/ui';

import internalPagesRoutes from '../../reactRoutes';
import BreadcrumbTrail from '../../BreadcrumbTrail';
import useNavigation from '../../Navigation/useNavigation';
import { federatedModulesAtom } from '../../federatedModules/atoms';
import { FederatedModule } from '../../federatedModules/models';
import { Remote } from '../../federatedModules/Load';

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
  federatedModules,
}): Array<JSX.Element> => {
  const isAllowedPage = (path): boolean =>
    allowedPages?.find((allowedPage) => path.includes(allowedPage));

  return federatedModules.map(
    ({ federatedPages, remoteEntry, moduleFederationName, moduleName }) => {
      return federatedPages?.map(({ component, route }) => {
        if (not(isAllowedPage(route))) {
          return null;
        }

        return (
          <Route
            element={
              <PageContainer>
                <BreadcrumbTrail path={route} />
                <Remote
                  component={component}
                  key={component}
                  moduleFederationName={moduleFederationName}
                  moduleName={moduleName}
                  remoteEntry={remoteEntry}
                />
              </PageContainer>
            }
            key={route}
            path={route}
          />
        );
      });
    },
  );
};

interface Props {
  allowedPages: Array<string | Array<string>>;
  externalPagesFetched: boolean;
  federatedModules: Array<FederatedModule>;
}

const ReactRouterContent = ({
  federatedModules,
  externalPagesFetched,
  allowedPages,
}: Props): JSX.Element => {
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
          {getExternalPageRoutes({ allowedPages, federatedModules })}
          {externalPagesFetched && (
            <Route element={<NotFoundPage />} path="*" />
          )}
        </Routes>
      </Suspense>
    ),
    memoProps: [externalPagesFetched, federatedModules, allowedPages],
  });
};

const ReactRouter = (): JSX.Element => {
  const federatedModules = useAtomValue(federatedModulesAtom);
  const { allowedPages } = useNavigation();

  const externalPagesFetched = not(isNil(federatedModules));

  if (!externalPagesFetched || !allowedPages) {
    return <PageSkeleton />;
  }

  return (
    <ReactRouterContent
      allowedPages={allowedPages}
      externalPagesFetched={externalPagesFetched}
      federatedModules={federatedModules as Array<FederatedModule>}
    />
  );
};

export default ReactRouter;
