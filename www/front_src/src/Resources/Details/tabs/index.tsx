import * as React from 'react';

import { isNil, find, propEq, invertObj, path, equals } from 'ramda';
import makeStyles from '@mui/styles/makeStyles';

import {
  labelDetails,
  labelGraph,
  labelTimeline,
  labelServices,
  labelMetrics,
} from '../../translatedLabels';
import { ResourceDetails } from '../models';
import DetailsLoadingSkeleton from '../LoadingSkeleton';

import { Tab, TabId } from './models';

const DetailsTab = React.lazy(() => import('./Details'));
const GraphTab = React.lazy(() => import('./Graph'));
const TimelineTab = React.lazy(() => import('./Timeline'));
const ServicesTab = React.lazy(() => import('./Services'));
const MetricsTab = React.lazy(() => import('./Metrics'));

const detailsTabId = 0;
const servicesTabId = 1;
const timelineTabId = 2;
const graphTabId = 3;
const metricsTabId = 4;

export interface TabProps {
  details?: ResourceDetails;
}

const tabs: Array<Tab> = [
  {
    Component: DetailsTab,
    getIsActive: (): boolean => true,
    id: detailsTabId,
    title: labelDetails,
  },
  {
    Component: ServicesTab,
    getIsActive: (details: ResourceDetails): boolean => {
      return details.type === 'host';
    },
    id: servicesTabId,
    title: labelServices,
  },
  {
    Component: TimelineTab,
    getIsActive: (): boolean => true,
    id: timelineTabId,
    title: labelTimeline,
  },
  {
    Component: GraphTab,
    getIsActive: (details: ResourceDetails): boolean => {
      if (isNil(details)) {
        return false;
      }

      if (equals(details.type, 'host')) {
        return true;
      }

      return !isNil(path(['links', 'endpoints', 'performance_graph'], details));
    },
    id: graphTabId,
    title: labelGraph,
  },
  {
    Component: MetricsTab,
    getIsActive: (details: ResourceDetails): boolean => {
      if (isNil(details)) {
        return false;
      }

      return details.type === 'metaservice';
    },
    id: metricsTabId,
    title: labelMetrics,
  },
];

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(2),
  },
}));

interface TabByIdProps {
  details?: ResourceDetails;
  id: number;
}

const TabById = ({ id, details }: TabByIdProps): JSX.Element | null => {
  const classes = useStyles();

  const { Component } = find(propEq('id', id), tabs) as Tab;

  return (
    <div className={classes.container}>
      <React.Suspense fallback={<DetailsLoadingSkeleton />}>
        <Component details={details} />
      </React.Suspense>
    </div>
  );
};

const tabIdByLabel = {
  details: detailsTabId,
  graph: graphTabId,
  metrics: metricsTabId,
  services: servicesTabId,
  timeline: timelineTabId,
};

const getTabIdFromLabel = (label: string): TabId => {
  const tabId = tabIdByLabel[label];

  if (isNil(tabId)) {
    return detailsTabId;
  }

  return tabId;
};

const getTabLabelFromId = (id: TabId): string => {
  return invertObj(tabIdByLabel)[id];
};

export {
  detailsTabId,
  timelineTabId,
  graphTabId,
  servicesTabId,
  metricsTabId,
  tabs,
  TabById,
  getTabIdFromLabel,
  getTabLabelFromId,
};
