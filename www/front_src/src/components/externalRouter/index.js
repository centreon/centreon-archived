import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { Switch, Route } from "react-router-dom";
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";
import centreonConfig from "../../config";
import NotAllowedPage from '../../route-components/notAllowedPage';
import styles from "../../styles/partials/_content.scss";

// class to dynamically import pages from modules
class ExternalRouter extends Component {

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

  render() {
    const { fetched } = this.props;
    const LoadableComponents = this.getLoadableComponents();

    return (
      <Suspense fallback="">
        <Switch>
          {LoadableComponents}
          {fetched &&
            <Route component={NotAllowedPage} />
          }
        </Switch>
      </Suspense>
    );
  };

}

const mapStateToProps = ({ navigation, externalComponents  }) => ({
  acl: navigation.acl,
  pages: externalComponents.pages,
  fetched: externalComponents.fetched,
});

export default connect(mapStateToProps)(ExternalRouter);