export interface Interval {
  end: number;
  start: number;
}

export interface GraphData {
  critical: Array<Interval>;
  ok: Array<Interval>;
  unknown: Array<Interval>;
  warning: Array<Interval>;
}
