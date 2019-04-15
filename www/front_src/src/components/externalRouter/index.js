import React, { Component, Suspense } from "react";
import { connect } from "react-redux";
import { Route } from "react-router-dom";
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";
import centreonConfig from "../../config";
import NotAllowedPage from '../../route-components/notAllowedPage';

// class to dynamically import pages from modules
class ExternalRouter extends Component {

  getLoadableComponents = () => {
    const { pages } = this.props;

    let LoadableComponents = [];
    for (const [path, parameter] of Object.entries(pages)) {
      const Page = React.lazy(() => dynamicImport(parameter));
      LoadableComponents.push(
        <Route
          path={path}
          exact="true"
          render={renderProps => (
            <Page
              centreonConfig={centreonConfig}
              centreonAxios={centreonAxios}
              {...renderProps}
            />
          )}
        />
      );
    }

    return LoadableComponents;
  }

  render() {
    const LoadableComponents = this.getLoadableComponents();

    return (
      <Suspense fallback="">
        {LoadableComponents}
        <Route component={NotAllowedPage} />
      </Suspense>
    );
  };

}

const mapStateToProps = ({ externalComponents }) => ({
  pages: externalComponents.pages
});

export default connect(mapStateToProps)(ExternalRouter);