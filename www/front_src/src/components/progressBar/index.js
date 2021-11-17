/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-indent */
/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React, { Component } from 'react';

import { connect } from 'react-redux';
import classnames from 'classnames';

import { Typography } from '@mui/material';

import { history } from '../../store';

import styles from './progressbar.scss';

class ProgressBar extends Component {
  goToPath = (path) => {
    history.push(path);
  };

  render() {
    const { links } = this.props;

    return (
      <div className={styles['progress-bar']}>
        <div className={styles['progress-bar-wrapper']}>
          <ul className={styles['progress-bar-items']}>
            {links
              ? links.map((link) => (
                  <li
                    className={styles['progress-bar-item']}
                    key={link.path}
                    onClick={this.goToPath.bind(this, link.path)}
                  >
                    <span
                      className={classnames(
                        styles['progress-bar-link'],
                        { [styles.active]: link.active },
                        { [styles.prev]: link.prevActive },
                      )}
                    >
                      <Typography>{link.number}</Typography>
                    </span>
                  </li>
                ))
              : null}
          </ul>
        </div>
      </div>
    );
  }
}
const mapStateToProps = () => ({});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(ProgressBar);
