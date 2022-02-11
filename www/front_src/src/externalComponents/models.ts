interface StaticJS {
  bundle: string;
  chunks: Array<string>;
  commons: Array<string>;
}

interface Files {
  css: Array<string>;
  js: StaticJS;
}

export interface ExternalComponent {
  [path: string]: Files;
}

interface ExternalComponents {
  hooks: ExternalComponent;
  pages: ExternalComponent;
}

export default ExternalComponents;
