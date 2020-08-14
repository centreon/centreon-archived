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
  id: TabId;
  Component: (props) => JSX.Element;
  title: string;
  getIsVisible: (endpoints) => boolean;
}

const tabs: Array<Tab> = [
  {
    id: detailsTabId,
    Component: DetailsTab,
    title: labelDetails,
    getIsVisible: (): boolean => true,
  },
  {
    id: timelineTabId,
    Component: TimelineTab,
    title: labelTimeline,
    getIsVisible: (): boolean => true,
  },
  {
    id: graphTabId,
    Component: GraphTab,
    title: labelGraph,
    getIsVisible: ({ endpoints }: ResourceLinks): boolean => {
      const { performanceGraph } = endpoints;
      return !isNil(performanceGraph);
    },
  },
  {
    id: shortcutsTabId,
    Component: ShortcutsTab,
    title: labelShortcuts,
    getIsVisible: (links: ResourceLinks): boolean => {
      const { parent, resource } = links.uris;

      return any(hasDefinedValues, [parent, resource]);
    },
  },
];

const getVisibleTabs = (links): Array<Tab> => {
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
