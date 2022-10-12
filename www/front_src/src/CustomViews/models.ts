import ReactGridLayout from 'react-grid-layout';

export interface WidgetConfiguration {
  moduleName: string;
  options?: object;
  path: string;
  widgetMinHeight?: number;
}

export interface WidgetLayout extends ReactGridLayout.Layout {
  widgetConfiguration: WidgetConfiguration;
}
