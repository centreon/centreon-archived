import ReactGridLayout from 'react-grid-layout';

export interface WidgetConfiguration {
  moduleName: string;
  options?: object;
  path: string;
}

export interface WidgetLayout extends ReactGridLayout.Layout {
  widgetConfiguration: WidgetConfiguration;
}
