import React, { Component } from "react";
import { Redirect } from "react-router-dom";

class ModuleRoute extends Component {
  state = { contentHeight: 100 };

  handleResize = () => {
    if (this.container) {
      const { contentWindow } = this.container;
      if (contentWindow) {
        const { documentElement } = contentWindow.document,
              { match } = this.props,
              { id } = match.params,
              contentHeight = Math.max(
                documentElement.clientHeight,
                documentElement.offsetHeight,
                documentElement.scrollHeight
              );
        if (contentHeight !== this.state.contentHeight)
          this.setState({ contentHeight });
      }
    }
  };

  onLoad = () => {
    if (this.container) {
      const { contentWindow } = this.container;
      if (contentWindow) {
        this.container.contentWindow.addEventListener(
          "resize",
          this.handleResize
        );
        this.handleResize();
      }
    }
  };

  componentWillMount = () => {
    this.setState({ contentHeight: 100 }, () => {
      setInterval(this.handleResize, 2000);
    });
  };

  componentWillUnmount() {
    if (this.container) {
      const { contentWindow } = this.container;
      if (contentWindow) {
        this.container.contentWindow.removeEventListener(
          "resize",
          this.handleResize
        );
      }
    }
  }

  render() {
    const { history } = this.props,
          { search } = history.location,
          { contentHeight } = this.state;
    return (
      <React.Fragment>
        {search ? (
          <iframe
            id="main-content"
            frameBorder="0"
            onLoad={this.onLoad}
            ref={container => {
              this.container = container;
            }}
            scrolling="no"
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
