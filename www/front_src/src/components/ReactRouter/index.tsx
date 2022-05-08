import { lazy, Suspense } from 'react';

import { Routes, Route } from 'react-router-dom';
import { isNil, not } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { styled } from '@mui/material';

import { PageSkeleton, useMemoComponent } from '@centreon/ui';

import internalPagesRoutes from '../../reactRoutes';
import BreadcrumbTrail from '../../BreadcrumbTrail';
import useNavigation from '../../Navigation/useNavigation';
import { federatedComponentsAtom } from '../../federatedModules/atoms';
import { FederatedComponent } from '../../federatedModules/models';
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
  federatedComponents,
}): Array<JSX.Element> => {
  const isAllowedPage = (path): boolean =>
    allowedPages?.find((allowedPage) => path.includes(allowedPage));

  return federatedComponents.map(({ pages, remoteEntry, name, moduleName }) => {
    return pages?.map(({ component, route }) => {
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
                moduleName={moduleName}
                name={name}
                remoteEntry={remoteEntry}
              />
            </PageContainer>
          }
          key={route}
          path={route}
        />
      );
    });
  });
};

interface Props {
  allowedPages: Array<string | Array<string>>;
  externalPagesFetched: boolean;
  federatedComponents: Array<FederatedComponent>;
}

const ReactRouterContent = ({
  federatedComponents,
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
          {getExternalPageRoutes({ allowedPages, federatedComponents })}
          {externalPagesFetched && (
            <Route element={<NotFoundPage />} path="*" />
          )}
        </Routes>
      </Suspense>
    ),
    memoProps: [externalPagesFetched, federatedComponents, allowedPages],
  });
};

const ReactRouter = (): JSX.Element => {
  const federatedComponents = useAtomValue(federatedComponentsAtom);
  const { allowedPages } = useNavigation();

  const externalPagesFetched = not(isNil(federatedComponents));

  if (!externalPagesFetched || !allowedPages) {
    return <PageSkeleton />;
  }

  return (
    <ReactRouterContent
      allowedPages={allowedPages}
      externalPagesFetched={externalPagesFetched}
      federatedComponents={federatedComponents as Array<FederatedComponent>}
    />
  );
};

export default ReactRouter;
