import { ListingModel } from '@centreon/ui';

import { NamedEntity, Status } from '../../../models';

export type ContactEntity = Omit<NamedEntity, 'uuid'>;

export interface NotificationsEvent {
  contact?: ContactEntity;
  contactGroup?: ContactEntity;
  content?: string;
  date: string;
  id: number;
  status?: Status;
  tries?: number;
}

export interface NotificationStatus {
  enable: boolean;
}

export type NotificationListing = ListingModel<NotificationsEvent>;
