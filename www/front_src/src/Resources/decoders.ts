import { JsonDecoder } from 'ts.data.json';

import { Status } from './models';

const statusDecoder = JsonDecoder.object<Status>(
  {
    name: JsonDecoder.string,
    severity_code: JsonDecoder.number,
  },
  'Status',
);

export { statusDecoder };
