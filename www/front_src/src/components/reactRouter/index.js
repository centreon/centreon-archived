/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-restricted-syntax */
/* eslint-disable react/prop-types */

import React, { Suspense } from 'react';
import { connect } from 'react-redux';
import { Switch, Route, withRouter } from 'react-router-dom';
import reactRoutes from '../../route-maps';
import { dynamicImport } from '../../helpers/dynamicImport';
import NotAllowedPage from '../../route-components/notAllowedPage';
import BreadcrumbWrapper from '../breadcrumbWrapper';
import styles from '../../styles/partials/_content.scss';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';

const LoadableComponents = ({
  history,
  isNavigationFetched,
  allowedPages,
  pages,
}) => {
  const basename = history.createHref({
    pathname: '/',
    search: '',
    hash: '',
  });

  // wait acl to add authorized routes
  if (!isNavigationFetched) {
    return null;
  }

  const pageEntries = Object.entries(pages);
  const isAllowedPage = (path) =>
    allowedPages.find((allowedPage) => path.includes(allowedPage));

  const loadablePages = pageEntries.filter(([path]) => isAllowedPage(path));

  return (
    <>
      {loadablePages.map(([path, parameter]) => {
        const Page = React.lazy(() => dynamicImport(basename, parameter));

        return (
          <Route
            key={path}
            path={path}
            exact
            render={(renderProps) => (
              <div className={styles['react-page']}>
                <BreadcrumbWrapper path={path}>
                  <Page {...renderProps} />
                </BreadcrumbWrapper>
              </div>
            )}
          />
        );
      })}
    </>
  );
};

// Component to manage internal react pages
const ReactRouter = React.memo(
  ({ isNavigationFetched, allowedPages, fetched, history, pages }) => {
    if (!isNavigationFetched || !fetched) {
      return null;
    }

    return (
      <Suspense fallback={null}>
        <Switch>
          {reactRoutes.map(({ path, comp: Comp, ...rest }) => (
            <Route
              key={path}
              path={path}
              exact
              render={(renderProps) => (
                <div className={styles['react-page']}>
                  {allowedPages.includes(path) ? (
                    <BreadcrumbWrapper path={path}>
                      <Comp {...renderProps} />
                    </BreadcrumbWrapper>
                  ) : (
                    <NotAllowedPage {...renderProps} />
                  )}
                </div>
              )}
              {...rest}
            />
          ))}
          <LoadableComponents
            history={history}
            isNavigationFetched={isNavigationFetched}
            allowedPages={allowedPages}
            pages={pages}
          />
          {fetched && (
            <Route
              component={
                NotAllowedPage // wait external components are fetched to avoid quick display of "not allowed" page
              }
            />
          )}
        </Switch>
      </Suspense>
    );
  },
  (previousProps, nextProps) => {
    const {
      pages: previousPages,
      allowedPages: previousAllowedPages,
    } = previousProps;
    const { pages: nextPages, allowedPages: nextAllowedPages } = nextProps;

    return (
      JSON.stringify(previousPages) === JSON.stringify(nextPages) &&
      JSON.stringify(previousAllowedPages) === JSON.stringify(nextAllowedPages)
    );
  },
);

const mapStateToProps = (state) => ({
  isNavigationFetched: state.navigation.fetched,
  allowedPages: allowedPagesSelector(state),
  pages: state.externalComponents.pages,
  fetched: state.externalComponents.fetched,
});

export default connect(mapStateToProps)(withRouter(ReactRouter));
