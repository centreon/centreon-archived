export interface FederatedModule {
  federatedComponentsConfiguration: {
    federatedComponents: Array<string>;
    path: string;
    widgetMinHeight?: number;
    widgetMinWidth?: { lg: number; md: number };
  };
  federatedPages: Array<PageComponent>;
  moduleFederationName: string;
  moduleName: string;
  remoteEntry: string;
}

interface PageComponent {
  component: string;
  route: string;
}
