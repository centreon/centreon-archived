import React, { Component } from "react";
import Loader from "../../components/loader";

class ModuleRoute extends Component {
  constructor(props) {
    super(props);

    this.mainContainer = null;
    this.resizeTimeout = null;

    this.state = {
      contentHeight: 0,
      loading: true
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
            loading: false,
            contentHeight: clientHeight - 30
          });
        }
      }, 200);
    }
  }

  handleHref = event => {
    let href = event.detail.href;
    // update route
    window.history.pushState(null, null, href);
  }

  // handle disconnect event sent by iframe
  handleDisconnect = event => {
    // update current url to redirect to login page
    window.location.href = event.detail.href;
  }

  componentDidMount() {
    this.mainContainer = window.parent.document.getElementById('fullscreen-wrapper');

    // add a listener on global page size
    window.parent.addEventListener(
      "resize",
      this.handleResize
    );

    // add event listener to update page url
    window.addEventListener(
      "react.href.update",
      this.handleHref,
      false
    );

    // add event listener to check if iframe is redirected to login page
    window.addEventListener(
      "react.href.disconnect",
      this.handleDisconnect,
      false
    );
  };

  componentWillUnmount() {
    clearTimeout(this.resizeTimeout);
    window.parent.removeEventListener(
      "resize",
      this.handleResize
    );

    window.parent.removeEventListener(
      "react.href.update",
      this.handleHref
    );

    window.parent.removeEventListener(
      "react.href.disconnect",
      this.handleDisconnect
    );
  }

  render() {
    const { contentHeight, loading } = this.state;
    const { history } = this.props,
          { search, hash } = history.location;
    let params;
    if (window['fullscreenSearch']) {
      params = window['fullscreenSearch'] + window['fullscreenHash']
    } else {
      params = (search || '') + (hash || '');
    }
    return (
      <>
        {loading &&
          <span className="main-loader">
            <Loader />
          </span>
        }
        <iframe
          id="main-content"
          title="Main Content"
          frameBorder="0"
          onLoad={this.handleResize}
          scrolling="yes"
          className={loading ? "hidden" : ""}
          style={{ width: "100%", height: `${contentHeight}px` }}
          src={`/_CENTREON_PATH_PLACEHOLDER_/main.get.php${params}`}
        />
      </>
    );
  }
}

export default ModuleRoute;
