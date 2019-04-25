import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { Switch, Route } from "react-router-dom";
import { history } from "../../store";
import { reactRoutes } from "../../route-maps";
import ReactRoute from '../router/reactRoute';
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";
import centreonConfig from "../../config";
import NotAllowedPage from '../../route-components/notAllowedPage';
import styles from "../../styles/partials/_content.scss";

// class to manage internal react pages
class ReactRouter extends Component {

  getLoadableComponents = () => {
    const { acl, pages } = this.props;
    let LoadableComponents = [];

    // wait acl to add authorized routes
    if (!acl.loaded) {
      return LoadableComponents;
    }

    for (const [path, parameter] of Object.entries(pages)) {

      // check if each acl route contains external page
      // eg: a user which have access to /configuration will have access to /configuration/hosts
      let isAllowed = false;
      for (const route of acl.routes) {
        if (path.includes(route)) {
          isAllowed = true;
        }
      }

      if (isAllowed) {
        const Page = React.lazy(() => dynamicImport(parameter));
        LoadableComponents.push(
          <Route
            path={path}
            exact="true"
            render={renderProps => (
              <div className={styles["react-page"]}>
                <Page
                  centreonConfig={centreonConfig}
                  centreonAxios={centreonAxios}
                  {...renderProps}
                />
              </div>
            )}
          />
        );
      }
    }

    return LoadableComponents;
  }

  shouldComponentUpdate(nextProps) {
    const { acl, fetched } = this.props;
    return (JSON.stringify(acl.routes) === JSON.stringify(nextProps.acl.routes)) || (fetched === false && nextProps.fetched === true);
    //return (acl.loaded === false && nextProps.acl.loaded === true) || (fetched === false && nextProps.fetched === true);
  }

  render() {
    const { acl, fetched } = this.props;

    if (!acl.loaded || !fetched) {
      return null;
    }

    const LoadableComponents = this.getLoadableComponents();

    return (
      <Suspense fallback="">
        {reactRoutes.map(({ path, comp, ...rest }) => (
          <ReactRoute
            history={history}
            path={path}
            exact="true"
            component={acl.routes.includes(path) ? comp : NotAllowedPage}
            {...rest}
          />
        ))}
        {LoadableComponents}
        {fetched &&
          <Route component={NotAllowedPage} />
        }
      </Suspense>
    );
  };
}

const mapStateToProps = ({ navigation, externalComponents }) => ({
  acl: navigation.acl,
  pages: externalComponents.pages,
  fetched: externalComponents.fetched,
});

export default connect(mapStateToProps)(ReactRouter);
