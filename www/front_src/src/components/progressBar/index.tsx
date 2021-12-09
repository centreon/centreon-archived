/* eslint-disable jsx-a11y/no-noninteractive-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */

import * as React from 'react';

import classnames from 'classnames';
import { useNavigate } from 'react-router';

import { Typography } from '@material-ui/core';

import styles from './progressbar.scss';

interface Props {
  links;
}

const ProgressBar = ({ links }: Props): JSX.Element => (
  <div className={styles['progress-bar']}>
    <div className={styles['progress-bar-wrapper']}>
      <ul className={styles['progress-bar-items']}>
        {links
          ? links.map((link) => (
              <li
                className={styles['progress-bar-item']}
                key={`${link.number}-${link.active}`}
              >
                <span
                  className={classnames(
                    styles['progress-bar-link'],
                    { [styles.active as string]: link.active },
                    { [styles.prev as string]: link.prevActive },
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

export default ProgressBar;
