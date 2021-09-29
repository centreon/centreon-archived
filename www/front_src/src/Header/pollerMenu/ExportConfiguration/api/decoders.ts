import { JsonDecoder } from 'ts.data.json';

import { StatusMessage } from '../models';

export const statusMessageDecoder = JsonDecoder.object<StatusMessage>(
  {
    message: JsonDecoder.nullable(JsonDecoder.string),
    status: JsonDecoder.number,
  },
  'Monitoring server',
);
