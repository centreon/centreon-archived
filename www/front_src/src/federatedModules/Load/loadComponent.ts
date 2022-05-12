/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck

import { ComponentType } from 'react';

const loadComponent = ({ moduleFederationName, component }) => {
  return async (): Promise<{
    default: ComponentType;
  }> => {
    // Initializes the share scope. This fills it with known provided modules from this build and all remotes
    await __webpack_init_sharing__('default');

    const container = window[moduleFederationName]; // or get the container somewhere else
    // Initialize the container, it may provide shared modules
    await container.init(__webpack_share_scopes__.default);

    const factory = await container.get(component);
    const Component = factory();

    return Component;
  };
};

export default loadComponent;
