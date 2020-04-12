export interface Metric {
  data: Array<number>;
  ds_data;
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
