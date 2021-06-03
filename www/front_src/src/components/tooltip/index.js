/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import { connect } from 'react-redux';
import { useTranslation } from 'react-i18next';
import classnames from 'classnames';

import styles from './tooltip.scss';

const Tooltip = ({ x, y, label, toggled }) => {
  const { t } = useTranslation();

  return (
    <div
      className={classnames(styles.tooltip, { [styles.hidden]: !toggled })}
      style={{
        left: x,
        top: y,
      }}
    >
      {t(label)}
    </div>
  );
};

/*
 * to make tooltip work we need the following key value pairs in order to map it's state to props
 * x for x mouse position
 * y for y mouse position
 * label for text that will be shown into the tooltip
 * toogled (bool) to detect if the menu is folded or not - toggled false - menu is folded
 */
const mapStateToProps = ({ tooltip }) => ({
  label: tooltip.label,
  toggled: tooltip.toggled,
  x: tooltip.x,
  y: tooltip.y,
});

export default connect(mapStateToProps, null)(Tooltip);
