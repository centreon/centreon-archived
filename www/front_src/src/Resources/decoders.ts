import { JsonDecoder } from 'ts.data.json';
import { Status } from './models';

const statusDecoder = JsonDecoder.object<Status>(
  {
    severity_code: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Status',
);

export { statusDecoder };
