/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */

import React from 'react';

import clsx from 'clsx';

import styles from './custom-subtitles.scss';

const Subtitle = ({ label, subtitleType, customSubtitleStyles }) => {
  const cn = clsx(
    styles['custom-subtitle'],
    styles[subtitleType],
    styles[customSubtitleStyles || ''],
  );

  return <span className={cn}>{label}</span>;
};

export default Subtitle;
