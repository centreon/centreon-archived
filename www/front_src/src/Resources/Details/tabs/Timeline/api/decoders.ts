import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { TimelineEvent, Status } from '../models';

const entityDecoder = JsonDecoder.object<TimelineEvent>(
  {
    id: JsonDecoder.number,
    type: JsonDecoder.string,
    content: JsonDecoder.string,
    date: JsonDecoder.string,
    startDate: JsonDecoder.optional(JsonDecoder.string),
    endDate: JsonDecoder.optional(JsonDecoder.string),
    tries: JsonDecoder.optional(JsonDecoder.string),
    authorName: JsonDecoder.optional(JsonDecoder.string),
    status: JsonDecoder.optional(
      JsonDecoder.object<Status>(
        {
          severityCode: JsonDecoder.number,
          name: JsonDecoder.string,
        },
        'TimelineEventStatus',
        {
          severityCode: 'severity_code',
        },
      ),
    ),
  },
  'TimelineEvent',
  {
    startDate: 'start_date',
    endDate: 'end_date',
    authorName: 'author_name',
  },
);

const listTimelineEventsDecoder = buildListingDecoder({
  entityDecoder,
  entityDecoderName: 'TimelineEvent',
  listingDecoderName: 'TimelineEvents',
});

export { listTimelineEventsDecoder };
