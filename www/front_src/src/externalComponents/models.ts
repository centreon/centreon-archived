import { path } from 'ramda';

interface StaticJS {
  bundle: string;
  chunks: Array<string>;
  commons: Array<string>;
}

interface Files {
  css: Array<string>;
  js: StaticJS;
}

interface ExternalComponent {
  [path: string]: Files;
}

interface ExternalComponents {
  hooks: ExternalComponent;
  pages: ExternalComponent;
}

export default ExternalComponents;
