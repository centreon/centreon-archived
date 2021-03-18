import { JsonDecoder } from 'ts.data.json';

import { CompactResource, Status } from './models';

const statusDecoder = JsonDecoder.object<Status>(
  {
    severity_code: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Status',
);

const resourceDecoder = JsonDecoder.object<CompactResource>(
  {
    status: statusDecoder,
    id: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Resource',
);

export { statusDecoder, resourceDecoder };
