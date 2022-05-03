import { lazy, Suspense } from 'react';

import { isNil, find, propEq, invertObj, path, equals } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import {
  labelDetails,
  labelGraph,
  labelTimeline,
  labelServices,
  labelMetrics,
  labelNotification,
} from '../../translatedLabels';
import { ResourceDetails } from '../models';
import DetailsLoadingSkeleton from '../LoadingSkeleton';

import { Tab, TabId } from './models';

const DetailsTab = lazy(() => import('./Details'));
const GraphTab = lazy(() => import('./Graph'));
const TimelineTab = lazy(() => import('./Timeline'));
const ServicesTab = lazy(() => import('./Services'));
const MetricsTab = lazy(() => import('./Metrics'));
const NotificationsTab = lazy(() => import('./Notifications'));

const detailsTabId = 0;
const servicesTabId = 1;
const timelineTabId = 2;
const graphTabId = 3;
const metricsTabId = 4;
const notificationsTabId = 5;

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
  {
    Component: NotificationsTab,
    getIsActive: (): boolean => true,
    id: notificationsTabId,
    title: labelNotification,
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
      <Suspense fallback={<DetailsLoadingSkeleton />}>
        <Component details={details} />
      </Suspense>
    </div>
  );
};

const tabIdByLabel = {
  details: detailsTabId,
  graph: graphTabId,
  metrics: metricsTabId,
  notification: notificationsTabId,
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
  notificationsTabId,
  tabs,
  TabById,
  getTabIdFromLabel,
  getTabLabelFromId,
};
