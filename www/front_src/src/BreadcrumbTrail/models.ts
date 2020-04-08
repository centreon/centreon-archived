export interface Breadcrumb {
  index?: number;
  link: string;
  label: string;
}

export interface BreadcrumbsByPath {
  [path: string]: Array<Breadcrumb>;
}
