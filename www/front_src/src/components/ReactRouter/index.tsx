import * as React from 'react';

import { connect } from 'react-redux';
import { Switch, Route, withRouter } from 'react-router-dom';
import { isEmpty, equals } from 'ramda';

import { styled } from '@material-ui/core';

import { PageSkeleton } from '@centreon/ui';

import internalPagesRoutes from '../../route-maps';
import { dynamicImport } from '../../helpers/dynamicImport';
import NotAllowedPage from '../../route-components/notAllowedPage';
import BreadcrumbTrail from '../../BreadcrumbTrail';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';

const PageContainer = styled('div')(({ theme }) => ({
  background: theme.palette.background.default,
  display: 'grid',
  gridTemplateRows: 'auto 1fr',
  height: '100%',
  overflow: 'auto',
}));

const getExternalPageRoutes = ({
  history,
  allowedPages,
  pages,
}): Array<JSX.Element> => {
  const basename = history.createHref({
    hash: '',
    pathname: '/',
    search: '',
  });

  const pageEntries = Object.entries(pages);
  const isAllowedPage = (path): boolean =>
    allowedPages.find((allowedPage) => path.includes(allowedPage));

  const loadablePages = pageEntries.filter(([path]) => isAllowedPage(path));

  return loadablePages.map(([path, parameter]) => {
    const Page = React.lazy(() => dynamicImport(basename, parameter));

    return (
      <Route
        exact
        key={path}
        path={path}
        render={(renderProps): JSX.Element => (
          <PageContainer>
            <BreadcrumbTrail path={path} />
            <Page {...renderProps} />
          </PageContainer>
        )}
      />
    );
  });
};

interface Props {
  allowedPages: Array<string>;
  externalPagesFetched: boolean;
  history;
  pages: Record<string, unknown>;
}

const ReactRouter = React.memo<Props>(
  ({ allowedPages, history, pages, externalPagesFetched }: Props) => {
    if (isEmpty(allowedPages)) {
      return <PageSkeleton />;
    }
    return (
      <React.Suspense fallback={<PageSkeleton />}>
        <Switch>
          {internalPagesRoutes.map(({ path, comp: Comp, ...rest }) => (
            <Route
              exact
              key={path}
              path={path}
              render={(renderProps): JSX.Element => (
                <PageContainer>
                  {allowedPages.includes(path) ? (
                    <>
                      <BreadcrumbTrail path={path} />
                      <Comp {...renderProps} />
                    </>
                  ) : (
                    <NotAllowedPage {...renderProps} />
                  )}
                </PageContainer>
              )}
              {...rest}
            />
          ))}
          {getExternalPageRoutes({ allowedPages, history, pages })}
          {externalPagesFetched && <Route component={NotAllowedPage} />}
        </Switch>
      </React.Suspense>
    );
  },
  (previousProps, nextProps) =>
    equals(previousProps.pages, nextProps.pages) &&
    equals(previousProps.allowedPages, nextProps.allowedPages) &&
    equals(previousProps.externalPagesFetched, nextProps.externalPagesFetched),
);

const mapStateToProps = (state): Record<string, unknown> => ({
  allowedPages: allowedPagesSelector(state),
  externalPagesFetched: state.externalComponents.fetched,
  pages: state.externalComponents.pages,
});

export default connect(mapStateToProps)(withRouter(ReactRouter));
