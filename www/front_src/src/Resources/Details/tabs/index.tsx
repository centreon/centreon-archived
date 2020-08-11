import * as React from 'react';

import {
  isNil,
  find,
  propEq,
  filter,
  reject,
  isEmpty,
  pipe,
  values,
  not,
} from 'ramda';

import { makeStyles } from '@material-ui/core';
import DetailsTab from './Details';
import {
  labelDetails,
  labelGraph,
  labelTimeline,
  labelShortcuts,
} from '../../translatedLabels';
import GraphTab from './Graph';
import { ResourceDetails } from '../models';
import { TabEndpoints, GraphEndpoints } from './models';
import TimelineTab from './Timeline';
import { ResourceLinks, ResourceUris } from '../../models';

const detailsTabId = 0;
const timelineTabId = 1;
const graphTabId = 2;
const shortcutsTabId = 3;

export type TabId = 0 | 1 | 2 | 3;

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
    visible: ({ endpoints }: ResourceLinks): boolean => {
      const { performanceGraph } = endpoints;
      return !isNil(performanceGraph);
    },
  },
  {
    id: shortcutsTabId,
    Component: () => <>Hello</>,
    title: labelShortcuts,
    visible: (links: ResourceLinks): boolean => {
      const hasDefinedUris = (uris: ResourceUris): boolean => {
        return pipe(values, filter(isNil), isEmpty, not)(uris);
      };

      const { parent, resource } = links.uris;

      return hasDefinedUris(parent) || hasDefinedUris(resource);
    },
  },
];

const getVisibleTabs = (links): Array<Tab> => {
  return tabs.filter(({ visible }) => visible(links));
};

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(1),
  },
}));

interface TabByIdProps {
  details?: ResourceDetails;
  id: number;
  links: ResourceLinks;
}

const TabById = ({ id, details, links }: TabByIdProps): JSX.Element | null => {
  const classes = useStyles();

  const { Component } = find(propEq('id', id), tabs) as Tab;

  return (
    <div className={classes.container}>
      <Component details={details} links={links} />
    </div>
  );
};

export {
  detailsTabId,
  timelineTabId,
  graphTabId,
  tabs,
  TabById,
  getVisibleTabs,
};
