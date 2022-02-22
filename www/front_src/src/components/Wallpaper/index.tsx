import * as React from 'react';

import { Fade } from '@mui/material';
import { makeStyles } from '@mui/styles';

import memoizeComponent from '../../Resources/memoizedComponent';
import centreonWallpaper from '../../assets/centreon-wallpaper.jpg';

import BackgroundImage, { defaultBackground } from './BackgroundImage';

const useStyles = makeStyles({
  placeholder: {
    background: defaultBackground,
    bottom: 0,
    left: 0,
    position: 'absolute',
    right: 0,
    top: 0,
  },
});

const loadImage = (): Promise<string> =>
  new Promise((resolve, reject) => {
    const image = new Image();

    image.src = centreonWallpaper;
    image.onload = (): void => resolve(centreonWallpaper);
    image.onerror = reject;
  });

const Wallpaper = (): JSX.Element => {
  const classes = useStyles();

  const [image, setImage] = React.useState<string | null>(null);

  loadImage()
    .then(setImage)
    .catch(() => undefined);

  return (
    <>
      <Fade in>
        <div className={classes.placeholder} />
      </Fade>
      <BackgroundImage image={image} />
    </>
  );
};

export default memoizeComponent({
  Component: Wallpaper,
  memoProps: [],
});
