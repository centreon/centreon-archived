import * as React from 'react';

import { Tabs, Tab, makeStyles, Grid, styled } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { labelDetails, labelGraph } from '../../translatedLabels';
import { DetailsSectionProps } from '..';
import GraphTab from './tabs/Graph';
import DetailsTab from './tabs/Details';
import { ResourceDetails } from '../models';

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

const tabs = [
  {
    key: 0,
    Component: DetailsTab,
    title: labelDetails,
  },
  {
    key: 1,
    Component: GraphTab,
    title: labelGraph,
  },
];

interface TabByIdProps {
  details: ResourceDetails;
  id: number;
}

const TabById = ({ id, details }: TabByIdProps): JSX.Element | null => {
  const { Component } = tabs[id];

  return <Component details={details} />;
};

type BodyContentProps = DetailsSectionProps & { selectedTabId: number };

const BodyContent = ({
  details,
  selectedTabId,
}: BodyContentProps): JSX.Element | null => {
  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  return <TabById id={selectedTabId} details={details} />;
};

const Body = ({ details }: DetailsSectionProps): JSX.Element => {
  const classes = useStyles();

  const [selectedTabId, setSelectedTabId] = React.useState(0);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };

  return (
    <div className={classes.body}>
      <Tabs
        variant="fullWidth"
        value={selectedTabId}
        indicatorColor="primary"
        textColor="primary"
        onChange={changeSelectedTabId}
      >
        {tabs.map(({ key, title }) => (
          <Tab key={key} label={title} disabled={details === undefined} />
        ))}
      </Tabs>
      <div className={classes.contentContainer}>
        <div className={classes.contentTab}>
          <BodyContent details={details} selectedTabId={selectedTabId} />
        </div>
      </div>
    </div>
  );
};

export default Body;
