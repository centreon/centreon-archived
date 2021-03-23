import { Status } from './models';

import { JsonDecoder } from 'ts.data.json';

const statusDecoder = JsonDecoder.object<Status>(
  {
    severity_code: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Status',
);

export { statusDecoder };
