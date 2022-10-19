import { useAtomValue } from 'jotai';
import { makeStyles } from 'tss-react/mui';

import { Fade } from '@mui/material';

import memoizeComponent from '../../Resources/memoizedComponent';

import BackgroundImage, { defaultBackground } from './BackgroundImage';
import { imageAtom } from './loadImageAtom';

const useStyles = makeStyles()({
  placeholder: {
    background: defaultBackground,
    bottom: 0,
    left: 0,
    position: 'absolute',
    right: 0,
    top: 0,
  },
});

const Wallpaper = (): JSX.Element => {
  const { classes } = useStyles();

  const image = useAtomValue(imageAtom);

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
