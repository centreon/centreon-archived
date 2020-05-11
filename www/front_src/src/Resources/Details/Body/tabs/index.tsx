import * as React from 'react';

import { isNil, find, propEq } from 'ramda';

import DetailsTab from './Details';
import { labelDetails, labelGraph } from '../../../translatedLabels';
import GraphTab from './Graph';
import { ResourceDetails } from '../../models';
import { TabEndpoints, GraphEndpoints } from '../models';

const detailsTabId = 0;
const graphTabId = 1;

export type TabId = 0 | 1;

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
    id: graphTabId,
    Component: GraphTab,
    title: labelGraph,
    visible: ({ statusGraph, performanceGraph }: GraphEndpoints): boolean =>
      !isNil(performanceGraph) || !isNil(statusGraph),
  },
];

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
  const { Component } = find(propEq('id', id), tabs) as Tab;

  return <Component details={details} endpoints={endpoints} />;
};

export { detailsTabId, graphTabId, tabs, TabById };
