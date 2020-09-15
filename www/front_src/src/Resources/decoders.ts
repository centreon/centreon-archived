import { JsonDecoder } from 'ts.data.json';

const statusDecoder = JsonDecoder.object<Status>(
  {
    severityCode: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Status',
  {
    severityCode: 'severity_code',
  },
);

export { statusDecoder };
