import * as React from 'react';

import { isNil } from 'ramda';
import { Responsive } from '@visx/visx';

import { styled, makeStyles } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { ResourceDetails } from '../../models';

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

interface Props {
  details?: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  if (isNil(details)) {
    return <LoadingSkeleton />;
  }

  return (
    <Responsive.ParentSize>
      {({ width }): JSX.Element => (
        <div>
          <SortableCards details={details} panelWidth={width} />
        </div>
      )}
    </Responsive.ParentSize>
  );
};

export default DetailsTab;
