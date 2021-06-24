import React, { Suspense } from 'react';

import { connect } from 'react-redux';
import { Switch, Route, withRouter } from 'react-router-dom';
import isEqual from 'lodash/isEqual';

import { styled } from '@material-ui/core';

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
    if (!externalPagesFetched) {
      // eslint-disable-next-line react/jsx-no-undef
      return <PageContainer />;
    }
    return (
      <Suspense fallback={null}>
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
      </Suspense>
    );
  },
  (previousProps, nextProps) =>
    isEqual(previousProps.pages, nextProps.pages) &&
    isEqual(previousProps.allowedPages, nextProps.allowedPages) &&
    isEqual(previousProps.externalPagesFetched, nextProps.externalPagesFetched),
);

const mapStateToProps = (state): Record<string, unknown> => ({
  allowedPages: allowedPagesSelector(state),
  externalPagesFetched: state.externalComponents.fetched,
  pages: state.externalComponents.pages,
});

export default connect(mapStateToProps)(withRouter(ReactRouter));
