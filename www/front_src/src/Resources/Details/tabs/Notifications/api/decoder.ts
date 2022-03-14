import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { statusDecoder } from '../../../../decoders';
import { ContactEntity, NotificationsEvent } from '../models';

const getContactEntityDecoder = (
  decoderName: string,
): JsonDecoder.Decoder<ContactEntity | undefined> =>
  JsonDecoder.optional(
    JsonDecoder.object(
      {
        id: JsonDecoder.number,
        name: JsonDecoder.string,
      },
      decoderName,
    ),
  );

const entityDecoder = JsonDecoder.object<NotificationsEvent>(
  {
    contact: getContactEntityDecoder('Contact'),
    contactGroup: getContactEntityDecoder('ContactGroup'),
    content: JsonDecoder.string,
    date: JsonDecoder.string,
    id: JsonDecoder.number,
    status: JsonDecoder.optional(statusDecoder),
    tries: JsonDecoder.optional(JsonDecoder.number),
  },
  'NotificationEvent',
);

const listNotificationsEventDecoder = buildListingDecoder({
  entityDecoder,
  entityDecoderName: 'NotificationsEvent',
  listingDecoderName: 'NotificationsEventListing',
});

export { listNotificationsEventDecoder };
