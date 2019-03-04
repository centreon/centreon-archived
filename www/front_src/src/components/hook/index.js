import React, { Component, Suspense } from "react";
import { dynamicImport } from "../../utils/dynamicImport";
import centreonAxios from "../../axios";

class Hook extends Component {

  state = {
    LoadableComponents: []
  };

  componentDidMount() {
    const { path } = this.props;
    centreonAxios("internal.php?object=centreon_frontend_hook&action=hooks&path=" + encodeURIComponent(path))
      .get()
      .then(({ data }) => {
        const rootUrl = window.location.pathname.split('/')[1];
        let LoadableComponents = [];
        for (const path of data) {
          LoadableComponents.push(
            React.lazy(
              () => dynamicImport('/' + rootUrl + path)
            )
          );
        }
        this.setState({
          LoadableComponents
        })
      })
      .catch((err) => {
        console.log(err);
      });
  };

  render() {
    const { path, ...props } = this.props;
    const { LoadableComponents } = this.state;

    return (
      <Suspense fallback="">
        {LoadableComponents.map(LoadableComponent => (
          <LoadableComponent
            centreonAxios={centreonAxios}
            {...props}
          />
        ))}
      </Suspense>
    );
  };

}

export default Hook;