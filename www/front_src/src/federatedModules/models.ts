export interface FederatedComponent {
  hooksConfiguration: {
    hooks: Array<string>;
    path: string;
  };
  moduleName: string;
  name: string;
  pages: Array<PageComponent>;
  remoteEntry: string;
}

interface PageComponent {
  component: string;
  route: string;
}
