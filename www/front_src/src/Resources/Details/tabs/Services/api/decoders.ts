import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { statusDecoder } from '../../../../decoders';
import { Service } from '../models';

const entityDecoder = JsonDecoder.object<Service>(
  {
    id: JsonDecoder.number,
    name: JsonDecoder.string,
    status: statusDecoder,
    output: JsonDecoder.string,
    duration: JsonDecoder.string,
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
