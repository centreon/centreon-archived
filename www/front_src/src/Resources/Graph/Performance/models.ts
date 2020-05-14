interface DsData {
  ds_color_line: string;
  ds_filled: boolean;
  ds_color_area: string;
  ds_transparency: number;
}

export interface Metric {
  data: Array<number>;
  ds_data: DsData;
  metric: string;
  unit: string;
  legend: string;
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
  name: string;
  color: string;
  metric: string;
  display: boolean;
  areaColor: string;
  unit: string;
  lineColor: string;
  filled: boolean;
  transparency: number;
  highlight?: boolean;
}
