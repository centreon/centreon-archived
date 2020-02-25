import React, { Suspense } from 'react';

import { connect } from 'react-redux';
import { Switch, Route, withRouter } from 'react-router-dom';
import isEmpty from 'lodash/isEmpty';
import isEqual from 'lodash/isEqual';

import { styled } from '@material-ui/core';

import internalPagesRoutes from '../../route-maps';
import { dynamicImport } from '../../helpers/dynamicImport';
import NotAllowedPage from '../../route-components/notAllowedPage';
import BreadcrumbWrapper from '../breadcrumbWrapper';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';

const PageContainer = styled('div')({
  overflow: 'auto',
  height: 'calc(100vh - 82px)',
});

const getExternalPageRoutes = ({
  history,
  allowedPages,
  pages,
}): Array<JSX.Element> => {
  const basename = history.createHref({
    pathname: '/',
    search: '',
    hash: '',
  });

  const pageEntries = Object.entries(pages);
  const isAllowedPage = (path): boolean =>
    allowedPages.find((allowedPage) => path.includes(allowedPage));

  const loadablePages = pageEntries.filter(([path]) => isAllowedPage(path));

  return loadablePages.map(([path, parameter]) => {
    const Page = React.lazy(() => dynamicImport(basename, parameter));

    return (
      <Route
        key={path}
        path={path}
        exact
        render={(renderProps): JSX.Element => (
          <PageContainer>
            <BreadcrumbWrapper path={path}>
              <Page {...renderProps} />
            </BreadcrumbWrapper>
          </PageContainer>
        )}
      />
    );
  });
};

interface Props {
  allowedPages: Array<string>;
  history;
  pages: {};
  externalPagesFetched: boolean;
}

const ReactRouter = React.memo<Props>(
  ({ allowedPages, history, pages, externalPagesFetched }: Props) => {
    if (isEmpty(allowedPages)) {
      return null;
    }
    return (
      <Suspense fallback={null}>
        <Switch>
          {internalPagesRoutes.map(({ path, comp: Comp, ...rest }) => (
            <Route
              key={path}
              path={path}
              exact
              render={(renderProps): JSX.Element => (
                <PageContainer>
                  {allowedPages.includes(path) ? (
                    <BreadcrumbWrapper path={path}>
                      <Comp {...renderProps} />
                    </BreadcrumbWrapper>
                  ) : (
                    <NotAllowedPage {...renderProps} />
                  )}
                </PageContainer>
              )}
              {...rest}
            />
          ))}
          {getExternalPageRoutes({ history, allowedPages, pages })}
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

const mapStateToProps = (state): {} => ({
  allowedPages: allowedPagesSelector(state),
  pages: state.externalComponents.pages,
  externalPagesFetched: state.externalComponents.fetched,
});

export default connect(mapStateToProps)(withRouter(ReactRouter));
