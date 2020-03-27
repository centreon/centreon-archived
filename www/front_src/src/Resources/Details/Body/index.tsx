import * as React from 'react';

import { Tabs, Tab, makeStyles, Grid, styled } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { labelDetails, labelGraph } from '../../translatedLabels';
import { DetailsSectionProps } from '..';
import DetailsTab from './DetailsTab';

const useStyles = makeStyles((theme) => {
  return {
    body: {
      display: 'grid',
      gridTemplateRows: 'auto 1fr',
      height: '100%',
    },
    contentContainer: {
      backgroundColor: theme.palette.background.default,
      position: 'relative',
    },
    contentTab: {
      position: 'absolute',
      bottom: 0,
      left: 0,
      right: 0,
      top: 0,
      overflowY: 'auto',
      overflowX: 'hidden',
      padding: 10,
    },
  };
});

const CardSkeleton = styled(Skeleton)(() => ({
  transform: 'none',
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container spacing={2} direction="column">
    <Grid item>
      <CardSkeleton height={120} />
    </Grid>
    <Grid item>
      <CardSkeleton height={75} />
    </Grid>
    <Grid item>
      <CardSkeleton height={75} />
    </Grid>
  </Grid>
);

const getTabById = ({ id, details }): JSX.Element | null => {
  const tabById = {
    0: <DetailsTab details={details} />,
  };

  return tabById[id];
};

const Body = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles();

  const [selectedTabId, setSelectedTabId] = React.useState(0);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };

  const loading = details === undefined;

  return (
    <div className={classes.body}>
      <Tabs
        variant="fullWidth"
        value={selectedTabId}
        indicatorColor="primary"
        textColor="primary"
        onChange={changeSelectedTabId}
      >
        <Tab label={labelDetails} disabled={loading} />
        <Tab label={labelGraph} disabled={loading} />
      </Tabs>
      <div className={classes.contentContainer}>
        <div className={classes.contentTab}>
          {loading ? (
            <LoadingSkeleton />
          ) : (
            getTabById({ id: selectedTabId, details })
          )}
        </div>
      </div>
    </div>
  );
};

export default Body;
