import * as React from 'react';

import { useAtomValue } from 'jotai';
import { not, path } from 'ramda';

import { useRequest, getData } from '@centreon/ui';

import { detailsAtom } from '../../detailsAtoms';
import InfiniteScroll from '../../InfiniteScroll';
import Events from '../Timeline/Events';

import { listNotificationsEventDecoder } from './api/decoder';
import { buildNotificationEndpoint } from './api';
import { NotificationListing, NotificationsEvent } from './models';

const limit = 30;

const NotificationTab = (): JSX.Element => {
  const { sendRequest, sending } = useRequest<NotificationListing>({
    decoder: listNotificationsEventDecoder,
    request: getData,
  });

  const details = useAtomValue(detailsAtom);
  const notificationsEndpoint = path(
    ['links', 'endpoints', 'notifications'],
    details,
  ) as string;

  const listNotificationsEvents = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<NotificationListing> => {
    return sendRequest({
      endpoint: buildNotificationEndpoint({
        endpoint: notificationsEndpoint,
        parameters: {
          limit,
          page: atPage,
          search: undefined,
        },
      }),
    });
  };

  return (
    <InfiniteScroll
      details={details}
      filter={undefined}
      limit={limit}
      loading={sending}
      loadingSkeleton={<p>Chargement des contacts...</p>}
      reloadDependencies={undefined}
      sendListingRequest={
        not(details?.notification_enabled) ? undefined : listNotificationsEvents
      }
    >
      {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
        return (
          <Events
            infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            timeline={entities}
          />
        );
      }}
    </InfiniteScroll>
  );
};

export default NotificationTab;
