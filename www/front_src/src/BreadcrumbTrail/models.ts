export interface Breadcrumb {
  index?: number;
  label: string;
  link: string;
}

export interface BreadcrumbsByPath {
  [path: string]: Array<Breadcrumb>;
}
