import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { statusDecoder } from '../../../../decoders';
import { Service } from '../models';

const entityDecoder = JsonDecoder.object<Service>(
  {
    duration: JsonDecoder.optional(JsonDecoder.string),
    id: JsonDecoder.number,
    name: JsonDecoder.string,
    output: JsonDecoder.string,
    status: statusDecoder,
  },
  'Service',
  {
    name: 'display_name',
  },
);

const listServicesDecoder = buildListingDecoder({
  entityDecoder,
  entityDecoderName: 'Service',
  listingDecoderName: 'Services',
});

export { listServicesDecoder };
