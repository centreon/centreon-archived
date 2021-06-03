import { ResourceEndpoints } from '../../models';

import { TabProps } from '.';

export type TabEndpoints = Omit<ResourceEndpoints, 'details'>;

export type TabId = 0 | 1 | 2 | 3 | 4 | 5;

export interface Tab {
  Component: (props: TabProps) => JSX.Element;
  getIsActive: (details) => boolean;
  id: TabId;
  title: string;
}
