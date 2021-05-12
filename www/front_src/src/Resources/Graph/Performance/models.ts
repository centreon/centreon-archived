interface DsData {
  ds_color_area: string;
  ds_color_line: string;
  ds_filled: boolean;
  ds_invert: string | null;
  ds_legend: string | null;
  ds_order: string | null;
  ds_stack: string | null;
  ds_transparency: number;
}

export interface Metric {
  average_value: number | null;
  data: Array<number>;
  ds_data: DsData;
  legend: string;
  maximum_value: number | null;
  metric: string;
  minimum_value: number | null;
  unit: string;
}

export interface GraphData {
  global;
  metrics: Array<Metric>;
  times: Array<string>;
}

export interface TimeValue {
  [field: string]: string | number;
  timeTick: string;
}

export interface Line {
  areaColor: string;
  average_value: number | null;
  color: string;
  display: boolean;
  filled: boolean;
  highlight?: boolean;
  invert: string | null;
  legend: string | null;
  lineColor: string;
  maximum_value: number | null;
  metric: string;
  minimum_value: number | null;
  name: string;
  stackOrder: number | null;
  transparency: number;
  unit: string;
}

export interface AdjustTimePeriodProps {
  end: Date;
  start: Date;
}

export enum GraphOptionId {
  displayEvents = 'displayEvents',
  displayTooltips = 'displayTooltips',
}
