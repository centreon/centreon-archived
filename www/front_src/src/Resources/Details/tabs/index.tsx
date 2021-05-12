import * as React from 'react';

import { isNil, find, propEq, any, invertObj, path } from 'ramda';

import { makeStyles } from '@material-ui/core';

import {
  labelDetails,
  labelGraph,
  labelTimeline,
  labelShortcuts,
  labelServices,
  labelMetrics,
} from '../../translatedLabels';
import { ResourceDetails } from '../models';
import hasDefinedValues from '../../hasDefinedValues';

import DetailsTab from './Details';
import GraphTab from './Graph';
import { Tab, TabId } from './models';
import TimelineTab from './Timeline';
import ShortcutsTab from './Shortcuts';
import ServicesTab from './Services';
// import MetricsTab from './Metrics';

const detailsTabId = 0;
const servicesTabId = 1;
const timelineTabId = 2;
const graphTabId = 3;
const metricsTabId = 4;
const shortcutsTabId = 5;

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

      return !isNil(path(['links', 'endpoints', 'performance_graph'], details));
    },
    id: graphTabId,
    title: labelGraph,
  },
  // {
  //   Component: MetricsTab,
  //   getIsActive: (details: ResourceDetails): boolean => {
  //     if (isNil(details)) {
  //       return false;
  //     }

  //     return details.type === 'metaservice';
  //   },
  //   id: metricsTabId,
  //   title: labelMetrics,
  // },
  {
    Component: ShortcutsTab,
    getIsActive: (details: ResourceDetails): boolean => {
      if (isNil(details)) {
        return false;
      }

      const { links, parent } = details;
      const parentUris = parent?.links?.uris;

      return any(hasDefinedValues, [parentUris, links.uris]);
    },
    id: shortcutsTabId,
    title: labelShortcuts,
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
      <Component details={details} />
    </div>
  );
};

const tabIdByLabel = {
  details: detailsTabId,
  graph: graphTabId,
  metrics: metricsTabId,
  services: servicesTabId,
  shortcuts: shortcutsTabId,
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
  shortcutsTabId,
  servicesTabId,
  metricsTabId,
  tabs,
  TabById,
  getTabIdFromLabel,
  getTabLabelFromId,
};
