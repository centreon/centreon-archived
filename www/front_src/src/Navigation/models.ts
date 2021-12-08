export interface Page {
  children?: Array<Page>;
  color?: string;
  groups?: Array<Page>;
  icon?: string;
  is_react?: boolean;
  label: string;
  menu_id?: string;
  options?: unknown;
  page?: string;
  show?: boolean;
  url?: string | null;
}

interface Navigation {
  result: Array<Page>;
  status: boolean;
}

export default Navigation;
