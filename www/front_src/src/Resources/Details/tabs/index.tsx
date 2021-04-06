import * as React from 'react';

import { isNil, find, propEq, any } from 'ramda';

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
import TimelineTab from './Timeline';
import { ResourceLinks } from '../../models';
import ShortcutsTab from './Shortcuts';
import hasDefinedValues from '../../hasDefinedValues';

const detailsTabId = 0;
const timelineTabId = 1;
const graphTabId = 2;
const shortcutsTabId = 3;

export type TabId = 0 | 1 | 2 | 3;

interface Tab {
  Component: (props) => JSX.Element;
  getIsVisible: (endpoints) => boolean;
  id: TabId;
  title: string;
}

const tabs: Array<Tab> = [
  {
    Component: DetailsTab,
    getIsVisible: (): boolean => true,
    id: detailsTabId,
    title: labelDetails,
  },
  {
    Component: TimelineTab,
    getIsVisible: (): boolean => true,
    id: timelineTabId,
    title: labelTimeline,
  },
  {
    Component: GraphTab,
    getIsVisible: ({ endpoints }: ResourceLinks): boolean => {
      const { performanceGraph } = endpoints;
      return !isNil(performanceGraph);
    },
    id: graphTabId,
    title: labelGraph,
  },
  {
    Component: ShortcutsTab,
    getIsVisible: (links: ResourceLinks): boolean => {
      const { parent, resource } = links.uris;

      return any(hasDefinedValues, [parent, resource]);
    },
    id: shortcutsTabId,
    title: labelShortcuts,
  },
];

const getVisibleTabs = (links: ResourceLinks): Array<Tab> => {
  return tabs.filter(({ getIsVisible }) => getIsVisible(links));
};

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(2),
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
  shortcutsTabId,
  tabs,
  TabById,
  getVisibleTabs,
};
