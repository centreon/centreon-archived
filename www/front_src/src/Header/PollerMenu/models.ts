export interface Issue {
  critical: number;
  total: number;
  warning: number;
}

export interface Issues {
  [key: string]: Issue;
}
