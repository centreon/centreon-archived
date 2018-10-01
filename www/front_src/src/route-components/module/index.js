import React, { Component } from "react";
import { Redirect } from "react-router-dom";

class ModuleRoute extends Component {
  constructor(props) {
    super(props);

    this.mainContainer = null;
    this.resizeTimeout = null;

    this.state = {
      contentHeight: 0
    }
  }

  handleResize = () => {
    // wait size is the same during 200ms to handle it
    clearTimeout(this.resizeTimeout);

    if (this.mainContainer) {
      this.resizeTimeout = setTimeout(() => {
        const { clientHeight } = this.mainContainer;
        const { contentHeight } = this.state;
        if (clientHeight != contentHeight) {
          this.setState({
            contentHeight: clientHeight
          });
        }
      }, 200);
    }
  }

  componentDidMount() {
    this.mainContainer = window.parent.document.getElementById('fullscreen-wrapper');

    // add a listener on global page size
    window.parent.addEventListener(
      "resize",
      this.handleResize
    );
  };

  componentWillUnmount() {
    clearTimeout(this.resizeTimeout);
    window.parent.removeEventListener(
      "resize",
      this.handleResize
    );
  }

  render() {
    const { contentHeight } = this.state
    const { history } = this.props,
          { search } = history.location;

    return (
      <React.Fragment>
        {search ? (
          <iframe
            id="main-content"
            title="Main Content"
            frameBorder="0"
            onLoad={this.handleResize}
            scrolling="yes"
            style={{ width: "100%", height: `${contentHeight}px` }}
            src={`/_CENTREON_PATH_PLACEHOLDER_/main.get.php${search}`}
          />
        ) : (
          <Redirect to={"/_CENTREON_PATH_PLACEHOLDER_/main.php?p=1"} />
        )}
      </React.Fragment>
    );
  }
}

export default ModuleRoute;
