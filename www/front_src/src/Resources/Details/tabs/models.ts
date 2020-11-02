import { ResourceEndpoints } from '../../models';

export type TabEndpoints = Omit<ResourceEndpoints, 'details'>;

export type TabId = 0 | 1 | 2 | 3 | 4;

export interface Tab {
  id: TabId;
  Component: (props: TabProps) => JSX.Element;
  title: string;
  getIsActive: (details) => boolean;
}
