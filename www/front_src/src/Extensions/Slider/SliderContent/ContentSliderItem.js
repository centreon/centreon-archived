/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import clsx from 'clsx';

import styles2 from './content-slider.scss';

const ContentSliderItem = ({ image, isActive }) => (
  <div
    alt="Slider image"
    className={clsx(
      styles2['content-slider-item'],
      styles2[isActive ? 'active-slide' : ''],
    )}
    style={{
      backgroundImage: `url(${image})`,
    }}
  />
);

export default ContentSliderItem;
