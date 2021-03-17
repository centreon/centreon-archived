import { JsonDecoder } from 'ts.data.json';

import { CompactParent, CompactResource, Status } from './models';

const statusDecoder = JsonDecoder.object<Status>(
  {
    severity_code: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Status',
);

const parentDecoder = JsonDecoder.object<CompactParent>(
  {
    status: statusDecoder,
    name: JsonDecoder.string,
    id: JsonDecoder.number,
  },
  'Parent',
);

const resourceDecoder = JsonDecoder.object<CompactResource>(
  {
    status: statusDecoder,
    id: JsonDecoder.number,
    name: JsonDecoder.string,
    parent: parentDecoder,
  },
  'Resource',
);

export { statusDecoder, resourceDecoder };
