import {
  labelUnhandled,
  labelAll,
  labelAcknowledged,
  labelInDowntime,
  labelHost,
  labelService,
} from '../translatedLabels';

const states = [
  { id: 'unhandled_problems', name: labelUnhandled },
  { id: 'all', name: labelAll },
  { id: 'acknowledged', name: labelAcknowledged },
  { id: 'in_downtime', name: labelInDowntime },
];

const resourceTypes = [
  { id: 'all', name: labelAll },
  { id: 'host', name: labelHost },
  { id: 'service', name: labelService },
];

const statuses = [
  { id: 'UP', name: 'Up' },
  { id: 'DOWN', name: 'Down' },
  { id: 'OK', name: 'Ok' },
  { id: 'WARNING', name: 'Warning' },
  { id: 'CRITICAL', name: 'Critical' },
  { id: 'UNKNOWN', name: 'Unknown' },
  { id: 'PENDING', name: 'Pending' },
  { id: 'UNREACHABLE', name: 'Unreachable' },
];

export { resourceTypes, states, statuses };
