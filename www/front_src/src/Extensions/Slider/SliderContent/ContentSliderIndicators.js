/* eslint-disable react/no-array-index-key */
/* eslint-disable jsx-a11y/no-static-element-interactions */
/* eslint-disable jsx-a11y/click-events-have-key-events */
/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import clsx from 'clsx';

import styles from './content-slider.scss';

const ContentSliderIndicators = ({ images, currentIndex, handleDotClick }) => {
  return (
    <div className={clsx(styles['content-slider-indicators'])}>
      {images.map((image, i) => (
        <span
          className={clsx(styles[i === currentIndex ? 'active' : 'dot'])}
          data-index={i}
          key={i}
          onClick={handleDotClick}
        />
      ))}
    </div>
  );
};

export default ContentSliderIndicators;
