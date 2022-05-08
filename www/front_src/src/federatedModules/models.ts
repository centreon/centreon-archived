export interface FederatedComponent {
  hooks: Array<string>;
  moduleName: string;
  name: string;
  pages: Array<PageComponent>;
  remoteEntry: string;
}

interface PageComponent {
  component: string;
  route: string;
}
