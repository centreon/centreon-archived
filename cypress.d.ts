import { MountOptions, MountReturn } from 'cypress/react';
import { MemoryRouterProps } from 'react-router-dom';

declare global {
  namespace Cypress {
    interface Chainable {
      mount: (
        component: React.ReactNode,
        options?: MountOptions & { routerProps?: MemoryRouterProps },
      ) => Cypress.Chainable<MountReturn>;
    }
  }
}
