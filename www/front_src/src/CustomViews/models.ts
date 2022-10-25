import ReactGridLayout from 'react-grid-layout';

export interface WidgetConfiguration {
  moduleName: string;
  options?: object;
  path: string;
  widgetMinHeight?: number;
  widgetMinWidth?: number;
}

export enum Breakpoint {
  lg = 'lg',
  md = 'md',
  sm = 'sm',
}

export interface WidgetLayout extends ReactGridLayout.Layout {
  widgetConfiguration: WidgetConfiguration;
}

export type ResponsiveWidgetLayout = {
  [key in Breakpoint]?: Array<WidgetLayout>;
};
