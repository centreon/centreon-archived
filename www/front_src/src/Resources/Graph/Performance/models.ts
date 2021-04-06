interface DsData {
  ds_color_area: string;
  ds_color_line: string;
  ds_filled: boolean;
  ds_transparency: number;
}

export interface Metric {
  data: Array<number>;
  ds_data: DsData;
  legend: string;
  metric: string;
  unit: string;
}

export interface GraphData {
  global;
  metrics: Array<Metric>;
  times: Array<string>;
}

export interface TimeValue {
  [field: string]: string | number;
}

export interface Line {
  areaColor: string;
  color: string;
  display: boolean;
  filled: boolean;
  highlight?: boolean;
  lineColor: string;
  metric: string;
  name: string;
  transparency: number;
  unit: string;
}
