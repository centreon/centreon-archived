import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { statusDecoder } from '../../../../decoders';
import { TimelineEvent, WithName } from '../models';

const getWithNameDecoder = (
  decoderName: string
): JsonDecoder.Decoder<WithName | undefined> =>
  JsonDecoder.optional(
    JsonDecoder.object(
      {
        name: JsonDecoder.string
      },
      decoderName
    )
  );

const entityDecoder = JsonDecoder.object<TimelineEvent>(
  {
    contact: getWithNameDecoder('Contact'),
    content: JsonDecoder.string,
    date: JsonDecoder.string,
    endDate: JsonDecoder.optional(JsonDecoder.string),
    id: JsonDecoder.number,
    startDate: JsonDecoder.optional(JsonDecoder.string),
    status: JsonDecoder.optional(statusDecoder),
    tries: JsonDecoder.optional(JsonDecoder.number),
    type: JsonDecoder.string
  },
  'TimelineEvent',
  {
    endDate: 'end_date',
    startDate: 'start_date'
  }
);

const listTimelineEventsDecoder = buildListingDecoder({
  entityDecoder,
  entityDecoderName: 'TimelineEvent',
  listingDecoderName: 'TimelineEvents'
});

export { listTimelineEventsDecoder };
