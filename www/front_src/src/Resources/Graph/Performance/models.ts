export interface Metric {
  data: Array<number>;
  ds_data;
  metric: string;
  unit: string;
}

export interface GraphData {
  global;
  metrics: Array<Metric>;
  times: Array<string>;
}

export interface MetricData {
  [metric: string]: string;
}

export interface TimeWithMetrics {
  [field: string]: string | number;
}
