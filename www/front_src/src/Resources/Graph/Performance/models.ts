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

export interface MetricData {
  [field: string]: string | number;
}
