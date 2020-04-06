export interface Interval {
  start: number;
  end: number;
}

export interface GraphData {
  critical: Array<Interval>;
  warning: Array<Interval>;
  ok: Array<Interval>;
  unknown: Array<Interval>;
}
