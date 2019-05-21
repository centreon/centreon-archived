import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { Switch, Route, withRouter } from "react-router-dom";
import { batchActions } from "redux-batched-actions";
import { fetchAclRoutes } from "../../redux/actions/navigationActions";
import { fetchExternalComponents } from "../../redux/actions/externalComponentsActions";
import { reactRoutes } from "../../route-maps";
import { dynamicImport } from "../../helpers/dynamicImport";
import centreonAxios from "../../axios";
import NotAllowedPage from '../../route-components/notAllowedPage';
import styles from "../../styles/partials/_content.scss";

// class to manage internal react pages
class ReactRouter extends Component {

  componentDidMount() {
    const { fetchAclRoutesAndExternalComponents } = this.props;

    // 1 - fetch allowed react routes
    // 2 - fetch external components (pages, hooks...)
    fetchAclRoutesAndExternalComponents();
  }

  getLoadableComponents = () => {
    const { history, acl, pages } = this.props;
    const basename = history.createHref({pathname: '/', search: '', hash: ''});
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
        const Page = React.lazy(() => dynamicImport(basename, parameter));
        LoadableComponents.push(
          <Route
            key={path}
            path={path}
            exact="true"
            render={renderProps => (
              <div className={styles["react-page"]}>
                <Page
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

  render() {
    const { acl, fetched } = this.props;

    if (!acl.loaded || !fetched) {
      return null;
    }

    const LoadableComponents = this.getLoadableComponents();

    return (
      <Suspense fallback={null}>
        <Switch>
          {reactRoutes.map(({ path, comp, ...rest }) => (
            <Route
              key={path}
              path={path}
              exact="true"
              component={acl.routes.includes(path) ? comp : NotAllowedPage}
              {...rest}
            />
          ))}
          {LoadableComponents}
          {fetched && // wait external components are fetched to avoid quick display of "not allowed" page
            <Route component={NotAllowedPage}/>
          }
        </Switch>
      </Suspense>
    );
  };
}

const mapStateToProps = ({ navigation, externalComponents }) => ({
  acl: navigation.acl,
  pages: externalComponents.pages,
  fetched: externalComponents.fetched,
});

const mapDispatchToProps = dispatch => {
  return {
    fetchAclRoutesAndExternalComponents: () => {
      // batch actions to avoid useless multiple rendering
      dispatch(batchActions([fetchAclRoutes(), fetchExternalComponents()]));
    }
  };
};

export default connect(mapStateToProps, mapDispatchToProps)(withRouter(ReactRouter));
