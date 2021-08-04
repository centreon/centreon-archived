/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prefer-stateless-function */

import React, { Component } from 'react';

import classnames from 'classnames';

import styles from './footer.scss';

class Footer extends Component {
  render() {
    return (
      <footer className={styles.footer}>
        <div className={styles['footer-wrap']}>
          <div className={styles['footer-wrap-left']} />
          <div className={styles['footer-wrap-middle']}>
            <ul
              className={classnames(
                styles['list-unstyled'],
                styles['footer-list'],
              )}
            >
              <li className={styles['footer-list-item']}>
                <a
                  href="https://documentation.centreon.com/"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  Documentation
                </a>
              </li>
              <li className={styles['footer-list-item']}>
                <a
                  href="https://support.centreon.com"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  Support
                </a>
              </li>
              <li className={styles['footer-list-item']}>
                <a
                  href="https://www.centreon.com"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  Centreon
                </a>
              </li>
              <li className={styles['footer-list-item']}>
                <a
                  href="https://github.com/centreon/centreon.git"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  Github Project
                </a>
              </li>
              <li className={styles['footer-list-item']}>
                <a
                  href="https://centreon.github.io"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  Slack
                </a>
              </li>
              <li className={styles['footer-list-item']}>
                <a
                  href="https://github.com/centreon/centreon/security/policy"
                  rel="noopener noreferrer"
                  target="_blank"
                >
                  Security Issue
                </a>
              </li>
            </ul>
          </div>
          <div className={styles['footer-wrap-right']}>
            <span>Copyright &copy; 2005 - 2021</span>
          </div>
        </div>
      </footer>
    );
  }
}

export default Footer;
