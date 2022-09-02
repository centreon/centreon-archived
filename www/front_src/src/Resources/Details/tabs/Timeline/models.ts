import { Status } from '../../../models';

export interface WithName {
  name: string;
}

export interface TimelineEvent {
  contact?: WithName;
  content: string;
  date: string;
  endDate?: string;
  id: number;
  startDate?: string;
  status?: Status;
  tries?: number;
  type: string;
}

export interface Type {
  id: string;
  name: string;
}
