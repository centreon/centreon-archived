/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/sort-comp */

import React, { Component } from 'react';
import classnames from 'classnames';
import styles from '../../components/header/header.scss';
import loaderStyles from '../../components/loader/loader.scss';
import Loader from '../../components/loader';

class LegacyRoute extends Component {
  constructor(props) {
    super(props);

    this.mainContainer = null;
    this.resizeTimeout = null;

    this.state = {
      contentHeight: 0,
      loading: true,
    };
  }

  handleResize = () => {
    // wait size is the same during 200ms to handle it
    clearTimeout(this.resizeTimeout);

    if (this.mainContainer) {
      this.resizeTimeout = setTimeout(() => {
        const { clientHeight } = this.mainContainer;
        const { contentHeight } = this.state;
        if (clientHeight !== contentHeight) {
          this.setState({
            loading: false,
            contentHeight: clientHeight - 30,
          });
        }
      }, 200);
    }
  };

  handleHref = (event) => {
    const { href } = event.detail;

    // update route
    window.history.pushState(null, null, href);
  };

  // handle disconnect event sent by iframe
  handleDisconnect = (event) => {
    // update current url to redirect to login page
    window.location.href = event.detail.href;
  };

  componentDidMount() {
    this.mainContainer = window.document.getElementById('fullscreen-wrapper');

    // add a listener on global page size
    window.addEventListener('resize', this.handleResize);

    // add event listener to update page url
    window.addEventListener('react.href.update', this.handleHref, false);

    // add event listener to check if iframe is redirected to login page
    window.addEventListener(
      'react.href.disconnect',
      this.handleDisconnect,
      false,
    );
  }

  componentWillUnmount() {
    clearTimeout(this.resizeTimeout);
    window.removeEventListener('resize', this.handleResize);

    window.removeEventListener('react.href.update', this.handleHref);

    window.removeEventListener('react.href.disconnect', this.handleDisconnect);
  }

  render() {
    const { contentHeight, loading } = this.state;
    const {
      history: {
        location: { search, hash },
      },
    } = this.props;

    let params;
    if (window.fullscreenSearch) {
      params = window.fullscreenSearch + window.fullscreenHash;
    } else {
      params = (search || '') + (hash || '');
    }

    return (
      <>
        {loading && (
          <span className={loaderStyles['main-loader']}>
            <Loader />
          </span>
        )}
        <iframe
          id="main-content"
          title="Main Content"
          frameBorder="0"
          onLoad={this.handleResize}
          scrolling="yes"
          className={classnames({ [styles.hidden]: loading })}
          style={{ width: '100%', height: `${contentHeight}px` }}
          src={`./main.get.php${params}`}
        />
      </>
    );
  }
}

export default LegacyRoute;
