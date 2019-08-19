/* eslint-disable react/jsx-filename-extension */
/* eslint-disable no-restricted-syntax */
/* eslint-disable react/prop-types */

import React, { Component, Suspense } from 'react';
import { connect } from 'react-redux';
import { Switch, Route, withRouter } from 'react-router-dom';
import { reactRoutes } from '../../route-maps';
import { dynamicImport } from '../../helpers/dynamicImport';
import centreonAxios from '../../axios';
import NotAllowedPage from '../../route-components/notAllowedPage';
import BreadcrumbWrapper from '../breadcrumbWrapper';
import styles from '../../styles/partials/_content.scss';
import { allowedPagesSelector } from "../../redux/selectors/navigation/allowedPages";

// class to manage internal react pages
class ReactRouter extends Component {
  getLoadableComponents = () => {
    const { history, isNavigationFetched, allowedPages, pages } = this.props;
    const basename = history.createHref({
      pathname: '/',
      search: '',
      hash: '',
    });
    const LoadableComponents = [];

    // wait acl to add authorized routes
    if (!isNavigationFetched) {
      return LoadableComponents;
    }

    for (const [path, parameter] of Object.entries(pages)) {
      // check if each acl route contains external page
      // eg: a user which have access to /configuration will have access to /configuration/hosts
      let isAllowed = false;
      for (const route of allowedPages) {
        if (path.includes(route)) {
          isAllowed = true;
        }
      }

      if (isAllowed) {
        const Page = React.lazy(() => dynamicImport(basename, parameter));
        LoadableComponents.push(
          <Route
            key={path}
            path={path}
            exact
            render={(renderProps) => (
              <div className={styles["react-page"]}>
                <BreadcrumbWrapper path={path}>
                  <Page
                    centreonAxios={centreonAxios}
                    {...renderProps}
                  />
                </BreadcrumbWrapper>
              </div>
            )}
          />,
        );
      }
    }

    return LoadableComponents;
  };

  render() {
    const { isNavigationFetched, allowedPages, fetched } = this.props;

    if (!isNavigationFetched || !fetched) {
      return null;
    }

    const LoadableComponents = this.getLoadableComponents();

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
                      <Comp
                        {...renderProps}
                      />
                    </BreadcrumbWrapper>
                  ) : (
                    <NotAllowedPage {...renderProps} />
                  )}
                </div>
              )}
              {...rest}
            />
          ))}
          {LoadableComponents}
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
  }
}

const mapStateToProps = (state) => ({
  isNavigationFetched: state.navigation.fetched,
  allowedPages: allowedPagesSelector(state),
  pages: state.externalComponents.pages,
  fetched: state.externalComponents.fetched,
});

export default connect(mapStateToProps)(withRouter(ReactRouter));
