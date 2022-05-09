import { FederatedComponent } from './models';

export const retrievedFederatedComponent: FederatedComponent = {
  hooksConfiguration: {
    hooks: ['./monitoring/hooks/topCounter'],
    path: '/header/topCounter',
  },
  moduleName: 'centreon-bam-server',
  name: 'bam',
  pages: [
    {
      component: './configuration/pages/bas',
      route: '/configuration/bam/bas',
    },
    {
      component: './configuration/pages/bvs',
      route: '/configuration/bam/bvs',
    },
  ],
  remoteEntry: 'remoteEntry.js',
};
