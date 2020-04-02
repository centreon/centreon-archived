import * as React from 'react';

import { isNil } from 'ramda';

import { Tabs, Tab, makeStyles, Grid, styled, AppBar } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import { labelDetails, labelGraph } from '../../translatedLabels';
import { DetailsSectionProps } from '..';
import GraphTab from './tabs/Graph';
import DetailsTab from './tabs/Details';
import { ResourceDetails } from '../models';
import { GraphEndpoints, TabEndpoints } from './models';

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
    visible: (): boolean => true,
  },
  {
    key: 1,
    Component: GraphTab,
    title: labelGraph,
    visible: ({ statusGraph, performanceGraph }: GraphEndpoints): boolean =>
      !isNil(performanceGraph) || !isNil(statusGraph),
  },
];

interface TabByIdProps {
  details: ResourceDetails;
  id: number;
  endpoints: TabEndpoints;
}

const TabById = ({
  id,
  details,
  endpoints,
}: TabByIdProps): JSX.Element | null => {
  const { Component } = tabs[id];

  return <Component details={details} endpoints={endpoints} />;
};

type BodyContentProps = DetailsSectionProps & {
  selectedTabId: number;
  endpoints: TabEndpoints;
};

const BodyContent = ({
  details,
  selectedTabId,
  endpoints,
}: BodyContentProps): JSX.Element | null => {
  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  return <TabById id={selectedTabId} details={details} endpoints={endpoints} />;
};

type Props = {
  endpoints: TabEndpoints;
} & DetailsSectionProps;

const Body = ({ details, endpoints }: Props): JSX.Element => {
  const classes = useStyles();

  const [selectedTabId, setSelectedTabId] = React.useState(0);

  const changeSelectedTabId = (_, id): void => {
    setSelectedTabId(id);
  };

  return (
    <div className={classes.body}>
      <AppBar position="static" color="default">
        <Tabs
          variant="fullWidth"
          value={selectedTabId}
          indicatorColor="primary"
          textColor="primary"
          onChange={changeSelectedTabId}
        >
          {tabs
            .filter(({ visible }) => visible(endpoints))
            .map(({ key, title }) => (
              <Tab key={key} label={title} disabled={details === undefined} />
            ))}
        </Tabs>
      </AppBar>
      <div className={classes.contentContainer}>
        <div className={classes.contentTab}>
          <BodyContent
            details={details}
            selectedTabId={selectedTabId}
            endpoints={endpoints}
          />
        </div>
      </div>
    </div>
  );
};

export default Body;
