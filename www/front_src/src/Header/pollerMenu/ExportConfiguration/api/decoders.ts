import { JsonDecoder } from 'ts.data.json';

import { buildListingDecoder } from '@centreon/ui';

import { MonitoringServer, StatusMessage } from '../models';

const monitoringServerDecoder = JsonDecoder.object<MonitoringServer>(
  {
    id: JsonDecoder.number,
    name: JsonDecoder.string,
  },
  'Monitoring server',
);

export const listMonitoringServersDecoder = buildListingDecoder({
  entityDecoder: monitoringServerDecoder,
  entityDecoderName: 'Monitoring server entity',
  listingDecoderName: 'List monitoring servers',
});

export const statusMessageDecoder = JsonDecoder.object<StatusMessage>(
  {
    message: JsonDecoder.nullable(JsonDecoder.string),
    status: JsonDecoder.number,
  },
  'Monitoring server',
);
