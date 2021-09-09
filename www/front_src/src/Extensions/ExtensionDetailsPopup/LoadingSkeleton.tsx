import * as React from 'react';

import { useTheme, makeStyles } from '@material-ui/core';
import { Skeleton, SkeletonProps } from '@material-ui/lab';

const headerHeight = 3.8;

const useStyles = makeStyles((theme) => ({
  nextContent: {
    marginTop: theme.spacing(1.5),
  },
}));

interface Props {
  animate?: boolean;
}

const BaseSkeleton = ({
  animate,
  ...props
}: Pick<Props, 'animate'> & SkeletonProps): JSX.Element => (
  <Skeleton animation={animate ? 'wave' : false} {...props} />
);

export const SliderSkeleton = ({ animate = true }: Props): JSX.Element => {
  const theme = useTheme();

  return (
    <BaseSkeleton
      animate={animate}
      height={theme.spacing(50)}
      variant="rect"
      width="100%"
    />
  );
};

export const HeaderSkeleton = ({ animate = true }: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

  return (
    <>
      <BaseSkeleton
        animate={animate}
        height={theme.spacing(headerHeight)}
        variant="rect"
        width={theme.spacing(10)}
      />
      <BaseSkeleton
        animate={animate}
        className={classes.nextContent}
        height={theme.spacing(headerHeight)}
        variant="rect"
        width={theme.spacing(20)}
      />
    </>
  );
};

export const ContentSkeleton = ({ animate = true }: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

  return (
    <>
      <BaseSkeleton
        animate={animate}
        variant="text"
        width={theme.spacing(20)}
      />
      <BaseSkeleton
        animate={animate}
        className={classes.nextContent}
        variant="text"
        width={theme.spacing(15)}
      />
      <BaseSkeleton
        animate={animate}
        className={classes.nextContent}
        variant="text"
        width={theme.spacing(25)}
      />
    </>
  );
};

export const ReleaseNoteSkeleton = ({ animate = true }: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

  return (
    <>
      <BaseSkeleton
        animate={animate}
        variant="text"
        width={theme.spacing(15)}
      />
      <BaseSkeleton
        animate={animate}
        className={classes.nextContent}
        variant="text"
        width={theme.spacing(25)}
      />
    </>
  );
};
