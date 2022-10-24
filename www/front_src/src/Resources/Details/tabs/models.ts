import { LazyExoticComponent } from 'react';

import { ResourceEndpoints } from '../../models';

import { TabProps } from '.';

export type TabEndpoints = Omit<ResourceEndpoints, 'details'>;

export enum TabId {
  detailsTabId = 0,
  servicesTabId = 1,
  timelineTabId = 2,
  graphTabId = 3,
  metricsTabId = 4,
  notificationsTabId = 5
}

export interface Tab {
  Component: LazyExoticComponent<(props: TabProps) => JSX.Element>;
  ariaLabel?: string;
  getIsActive: (details) => boolean;
  id: TabId;
  title: string;
}
