/* eslint-disable react/jsx-filename-extension */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/prop-types */

import React from 'react';

import clsx from 'clsx';

import styles from './content-slider.scss';

const ContentSliderLeftArrow = ({ goToPrevSlide, iconColor }) => {
  return (
    <span
      className={clsx(styles['content-slider-prev'])}
      onClick={goToPrevSlide}
    >
      <span
        className={clsx(
          styles['content-slider-prev-icon'],
          styles[iconColor || ''],
        )}
      />
    </span>
  );
};

export default ContentSliderLeftArrow;
