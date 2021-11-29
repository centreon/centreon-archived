import * as React from 'react';

import { equals, isNil } from 'ramda';
import { Responsive } from '@visx/visx';
import { useAtomValue } from 'jotai/utils';

import { styled, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { detailsAtom } from '../../detailsAtoms';

import SortableCards from './SortableCards';

const useStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
    gridTemplateRows: '67px',
  },
}));

const CardSkeleton = styled(Skeleton)(() => ({
  transform: 'none',
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.loadingSkeleton}>
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
    </div>
  );
};

const DetailsTab = (): JSX.Element => {
  const details = useAtomValue(detailsAtom);

  return (
    <Responsive.ParentSize>
      {({ width }): JSX.Element => {
        const loading = isNil(details) || equals(width, 0);

        if (loading) {
          return <LoadingSkeleton />;
        }

        return (
          <div>
            <SortableCards details={details} panelWidth={width} />
          </div>
        );
      }}
    </Responsive.ParentSize>
  );
};

export default DetailsTab;
