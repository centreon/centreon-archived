export default Loader;
import React, {Component} from 'preact';

class Loader extends Component {

  render() {
    return (
      <div className="loader">
        <div className="loader-inner ball-grid-pulse">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        </div>
      </div>
    );
  }
}

export default Loader;
