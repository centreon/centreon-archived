import { atom } from 'jotai';
import { atomWithDefault } from 'jotai/utils';
import {
  always,
  cond,
  dec,
  equals,
  filter,
  find,
  findIndex,
  gt,
  gte,
  isEmpty,
  isNil,
  keys,
  length,
  Lens,
  lensPath,
  lensProp,
  map,
  max,
  propEq,
  reduce,
  reject,
  set,
  T,
  update,
} from 'ramda';

import {
  Breakpoint,
  ResponsiveWidgetLayout,
  WidgetConfiguration,
  WidgetLayout,
} from './models';

export const getBreakpoint = cond<[width: number], Breakpoint>([
  [gt(1000), always(Breakpoint.sm)],
  [gt(1500), always(Breakpoint.md)],
  [T, always(Breakpoint.lg)],
]);

export const getDefaultColumnsByBreakpoint = cond([
  [equals('sm'), always(3)],
  [equals('md'), always(6)],
  [T, always(12)],
]);

export const breakpointAtom = atomWithDefault<Breakpoint>(() =>
  getBreakpoint(window.innerWidth),
);

export const columnsAtom = atom((get) => {
  return getDefaultColumnsByBreakpoint(get(breakpointAtom));
});

export const responsiveLayoutAtom = atom<ResponsiveWidgetLayout>({
  [Breakpoint.sm]: [],
  [Breakpoint.md]: [],
  [Breakpoint.lg]: [],
});

export const isEditingAtom = atom(false);

export const layoutByBreakpointDerivedAtom = atom(
  (get) => {
    const breakpoint = get(breakpointAtom);

    return get(responsiveLayoutAtom)?.[breakpoint] || [];
  },
  (get, setAtom, newLayout: Array<WidgetLayout>) => {
    const breakpoint = get(breakpointAtom);
    const responsiveLayout = get(responsiveLayoutAtom);

    const newResponsiveLayout = isNil(responsiveLayout)
      ? responsiveLayout
      : set(lensProp(breakpoint), newLayout, responsiveLayout);

    setAtom(responsiveLayoutAtom, newResponsiveLayout);
  },
);

export const setLayoutModeDerivedAtom = atom(
  null,
  (get, setAtom, isEditing: boolean) => {
    setAtom(isEditingAtom, isEditing);

    const layouts = get(responsiveLayoutAtom);

    const newLayout = isNil(layouts)
      ? layouts
      : reduce<[string, Array<WidgetLayout>], ResponsiveWidgetLayout>(
          (acc, [key, layout]) => ({
            ...acc,
            [key]: map<WidgetLayout, WidgetLayout>(
              set(lensProp('static'), !isEditing),
              layout,
            ),
          }),
          {},
          Object.entries(layouts),
        );

    setAtom(responsiveLayoutAtom, newLayout);
  },
);

export const addWidgetDerivedAtom = atom(
  null,
  (get, setAtom, widgetConfiguration: WidgetConfiguration) => {
    const responsiveLayout = get(responsiveLayoutAtom);
    const currentLayout = get(layoutByBreakpointDerivedAtom);
    const columns = get(columnsAtom);

    const title = `Widget ${length(currentLayout)}`;

    const widgetMinWith = widgetConfiguration?.widgetMinWidth || columns;

    const widgetWidth = gt(widgetMinWith, columns) ? columns : widgetMinWith;

    const baseWidgetLayout = {
      h: widgetConfiguration?.widgetMinHeight || 4,
      i: title,
      minH: widgetConfiguration?.widgetMinHeight || 4,
      minW: widgetConfiguration?.widgetMinWidth || 1,
      static: false,
      w: widgetWidth,
      widgetConfiguration,
    };

    const updateResponsiveLayoutAtom = ({ x, y }): void => {
      const newResponsiveLayout = reduce<Breakpoint, ResponsiveWidgetLayout>(
        (acc, key) => ({
          ...acc,
          [key]: [
            ...(responsiveLayout[key] || []),
            {
              ...baseWidgetLayout,
              x: gte(x, getDefaultColumnsByBreakpoint(key))
                ? max(0, x - dec(columns))
                : x,
              y:
                gte(x, getDefaultColumnsByBreakpoint(key)) ||
                equals(getDefaultColumnsByBreakpoint(key), 1)
                  ? Math.max(
                      0,
                      ...map(
                        ({ y: widgetY, h }) => widgetY + h,
                        responsiveLayout[key] as Array<WidgetLayout>,
                      ),
                    )
                  : y,
            },
          ],
        }),
        {},
        keys(responsiveLayout),
      );

      setAtom(responsiveLayoutAtom, newResponsiveLayout);
    };

    if (isEmpty(currentLayout)) {
      updateResponsiveLayoutAtom({ x: 0, y: 0 });

      return;
    }

    const maxYWithHeight = Math.max(...map(({ y, h }) => y + h, currentLayout));
    const maxY = Math.max(...map(({ y }) => y, currentLayout));

    const lastLineWidgets = filter(
      ({ h, y }) => equals(maxYWithHeight, y + h),
      currentLayout,
    );

    const maxXFromLastLineWidgets = Math.max(
      ...map(({ x, w }) => x + w, lastLineWidgets),
    );

    if (equals(maxXFromLastLineWidgets, columns)) {
      updateResponsiveLayoutAtom({ x: 0, y: maxYWithHeight });

      return;
    }

    updateResponsiveLayoutAtom({
      x: maxXFromLastLineWidgets,
      y: maxY,
    });
  },
);

export const removeWidgetDerivedAtom = atom(
  null,
  (get, setAtom, widgetKey: string) => {
    const layouts = get(responsiveLayoutAtom);

    const newLayout = isNil(layouts)
      ? layouts
      : reduce<[string, Array<WidgetLayout>], ResponsiveWidgetLayout>(
          (acc, [key, layout]) => ({
            ...acc,
            [key]: reject(propEq('i', widgetKey), layout),
          }),
          {},
          Object.entries(layouts),
        );

    setAtom(responsiveLayoutAtom, newLayout);
  },
);

export const getWidgetOptionsDerivedAtom = atom(
  (get) =>
    (title: string): object | null => {
      const currentLayout = get(layoutByBreakpointDerivedAtom);
      const widget = find(propEq('i', title), currentLayout);

      if (isNil(widget)) {
        return null;
      }

      return widget.widgetConfiguration.options || null;
    },
);

interface SetWidgetOptionsProps {
  options: object;
  title: string;
}

export const setWidgetOptionsDerivedAtom = atom(
  null,
  (_, setAtom, { title, options }: SetWidgetOptionsProps) => {
    const updateWidget = (widgets): WidgetLayout | undefined => {
      const widget = find<WidgetLayout>(
        propEq('i', title),
        widgets,
      ) as WidgetLayout;

      if (isNil(widget?.widgetConfiguration)) {
        return widget;
      }

      const newWidget = set(
        lensPath(['widgetConfiguration', 'options']) as Lens<
          WidgetLayout,
          object
        >,
        options,
        widget,
      );

      return newWidget;
    };

    setAtom(responsiveLayoutAtom, (currentLayouts) =>
      isNil(currentLayouts)
        ? currentLayouts
        : reduce<[string, Array<WidgetLayout>], ResponsiveWidgetLayout>(
            (acc, [key, layout]) => {
              const widgetIndex = findIndex(propEq('i', title), layout);

              return {
                ...acc,
                [key]: update(widgetIndex, updateWidget(layout), layout),
              };
            },
            {},
            Object.entries(currentLayouts),
          ),
    );
  },
);

export const duplicateWidgetDerivedAtom = atom(
  null,
  (get, setAtom, title: string) => {
    const widget = find(propEq('i', title), get(layoutByBreakpointDerivedAtom));

    if (isNil(widget)) {
      return;
    }

    setAtom(addWidgetDerivedAtom, widget.widgetConfiguration);
  },
);

export const changeLayoutDerivedAtom = atom(
  null,
  (_, setAtom, { breakpoint }) => {
    setAtom(breakpointAtom, breakpoint);
  },
);
