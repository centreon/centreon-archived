import * as React from 'react';

import { ResourceEndpoints } from '../../models';

import { TabProps } from '.';

export type TabEndpoints = Omit<ResourceEndpoints, 'details'>;

export type TabId = 0 | 1 | 2 | 3 | 4| 5;

export interface Tab {
  Component: React.LazyExoticComponent<(props: TabProps) => JSX.Element>;
  ariaLabel?: string;
  getIsActive: (details) => boolean;
  id: TabId;
  title: string;
}
