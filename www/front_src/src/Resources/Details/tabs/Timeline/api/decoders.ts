import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { statusDecoder } from '../../../../decoders';
import { TimelineEvent, WithName } from '../models';

const getWithNameDecoder = (
  decoderName: string,
): JsonDecoder.Decoder<WithName | undefined> =>
  JsonDecoder.optional(
    JsonDecoder.object(
      {
        name: JsonDecoder.string,
      },
      decoderName,
    ),
  );

const entityDecoder = JsonDecoder.object<TimelineEvent>(
  {
    id: JsonDecoder.number,
    type: JsonDecoder.string,
    content: JsonDecoder.string,
    date: JsonDecoder.string,
    startDate: JsonDecoder.optional(JsonDecoder.string),
    endDate: JsonDecoder.optional(JsonDecoder.string),
    tries: JsonDecoder.optional(JsonDecoder.number),
    contact: getWithNameDecoder('Contact'),
    status: JsonDecoder.optional(statusDecoder),
  },
  'TimelineEvent',
  {
    startDate: 'start_date',
    endDate: 'end_date',
  },
);

const listTimelineEventsDecoder = buildListingDecoder({
  entityDecoder,
  entityDecoderName: 'TimelineEvent',
  listingDecoderName: 'TimelineEvents',
});

export { listTimelineEventsDecoder };
