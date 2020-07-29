import { monitoringEndpoint, resourcesEndpoint } from '../../api/endpoint';

const acknowledgeEndpoint = `${resourcesEndpoint}/acknowledge`;
const downtimeEndpoint = `${resourcesEndpoint}/downtime`;
const hostEndpoint = `${monitoringEndpoint}/hosts`;
const hostCheckEndpoint = `${hostEndpoint}/check`;
const serviceEndpoint = `${monitoringEndpoint}/services`;
const serviceCheckEndpoint = `${serviceEndpoint}/check`;

export {
  acknowledgeEndpoint,
  downtimeEndpoint,
  hostCheckEndpoint,
  serviceCheckEndpoint,
};
