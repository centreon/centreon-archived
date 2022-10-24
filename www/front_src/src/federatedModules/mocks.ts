import { FederatedModule } from './models';

export const retrievedFederatedModule: FederatedModule = {
  federatedComponentsConfiguration: {
    federatedComponents: ['./monitoring/hooks/topCounter'],
    path: '/header/topCounter'
  },
  federatedPages: [
    {
      component: './configuration/pages/bas',
      route: '/configuration/bam/bas'
    },
    {
      component: './configuration/pages/bvs',
      route: '/configuration/bam/bvs'
    }
  ],
  moduleFederationName: 'bam',
  moduleName: 'centreon-bam-server',
  remoteEntry: 'remoteEntry.js'
};
