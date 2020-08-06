import * as React from 'react';

import { isNil, find, propEq } from 'ramda';

import { makeStyles } from '@material-ui/core';
import DetailsTab from './Details';
import {
  labelDetails,
  labelGraph,
  labelTimeline,
} from '../../translatedLabels';
import GraphTab from './Graph';
import { ResourceDetails } from '../models';
import { TabEndpoints, GraphEndpoints } from './models';
import TimelineTab from './Timeline';

const detailsTabId = 0;
const timelineTabId = 1;
const graphTabId = 2;

export type TabId = 0 | 1 | 2;

interface Tab {
  id: TabId;
  Component: (props) => JSX.Element;
  title: string;
  visible: (endpoints) => boolean;
}

const tabs: Array<Tab> = [
  {
    id: detailsTabId,
    Component: DetailsTab,
    title: labelDetails,
    visible: (): boolean => true,
  },
  {
    id: timelineTabId,
    Component: TimelineTab,
    title: labelTimeline,
    visible: (): boolean => true,
  },
  {
    id: graphTabId,
    Component: GraphTab,
    title: labelGraph,
    visible: ({ statusGraph, performanceGraph }: GraphEndpoints): boolean =>
      !isNil(performanceGraph) || !isNil(statusGraph),
  },
];

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(1),
  },
}));

interface TabByIdProps {
  details?: ResourceDetails;
  id: number;
  endpoints: TabEndpoints;
}

const TabById = ({
  id,
  details,
  endpoints,
}: TabByIdProps): JSX.Element | null => {
  const classes = useStyles();

  const { Component } = find(propEq('id', id), tabs) as Tab;

  return (
    <div className={classes.container}>
      <Component details={details} endpoints={endpoints} />
    </div>
  );
};

export { detailsTabId, timelineTabId, graphTabId, tabs, TabById };
