import { resourcesEndpoint } from '../../api/endpoint';

const acknowledgeEndpoint = `${resourcesEndpoint}/acknowledge`;
const downtimeEndpoint = `${resourcesEndpoint}/downtime`;
const checkEndpoint = `${resourcesEndpoint}/check`;

export { acknowledgeEndpoint, downtimeEndpoint, checkEndpoint };
