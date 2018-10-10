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
    const { contentHeight, loading } = this.state
    const { history } = this.props,
          { search } = history.location;
    const params = search || '';
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
