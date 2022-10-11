import { atom } from 'jotai';
import {
  equals,
  filter,
  find,
  findIndex,
  isEmpty,
  isNil,
  length,
  lensPath,
  lensProp,
  map,
  propEq,
  reject,
  set,
  update,
} from 'ramda';

import { WidgetConfiguration, WidgetLayout } from './models';

export const columnsAtom = atom(3);

export const layoutAtom = atom<Array<WidgetLayout>>([]);

export const isEditingAtom = atom(false);

export const setLayoutModeDerivedAtom = atom(
  null,
  (get, setAtom, isEditing: boolean) => {
    setAtom(isEditingAtom, isEditing);

    const newLayout = map(set(lensProp('static'), !isEditing), get(layoutAtom));

    setAtom(layoutAtom, newLayout);
  },
);

export const addWidgetDerivedAtom = atom(
  null,
  (get, setAtom, widgetConfiguration: WidgetConfiguration) => {
    const currentLayout = get(layoutAtom);
    const columns = get(columnsAtom);

    const title = `Widget ${length(currentLayout)}`;

    const baseWidgetLayout = {
      h: 4,
      i: title,
      minH: 4,
      static: false,
      w: 1,
      widgetConfiguration,
    };

    if (isEmpty(currentLayout)) {
      setAtom(layoutAtom, [
        {
          ...baseWidgetLayout,
          x: 0,
          y: 0,
        },
      ]);

      return;
    }

    const maxY = Math.max(...map(({ y, h }) => y + h, currentLayout));
    const lastLineWidgets = filter(
      ({ h, y }) => equals(maxY, y + h),
      currentLayout,
    );
    const maxXFromLastLineWidgets = Math.max(
      ...map(({ x, w }) => x + w, lastLineWidgets),
    );

    if (equals(maxXFromLastLineWidgets, columns)) {
      setAtom(layoutAtom, [
        ...currentLayout,
        {
          ...baseWidgetLayout,
          x: 0,
          y: maxY + 1,
        },
      ]);

      return;
    }

    setAtom(layoutAtom, [
      ...currentLayout,
      {
        ...baseWidgetLayout,
        x: maxXFromLastLineWidgets,
        y: maxY,
      },
    ]);
  },
);

export const removeWidgetDerivedAtom = atom(
  null,
  (_, setAtom, widgetKey: string) => {
    setAtom(layoutAtom, (currentLayout) =>
      reject(propEq('i', widgetKey), currentLayout),
    );
  },
);

export const getWidgetOptionsDerivedAtom = atom(
  (get) =>
    (title: string): object | null => {
      const widget = find(propEq('i', title), get(layoutAtom));

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
  (get, setAtom, { title, options }: SetWidgetOptionsProps) => {
    const widgets = get(layoutAtom);
    const widget = find(propEq('i', title), widgets);
    const widgetIndex = findIndex(propEq('i', title), widgets);

    if (isNil(widget)) {
      return;
    }

    const newWidget = set(
      lensPath(['widgetConfiguration', 'options']),
      options,
      widget,
    );

    setAtom(layoutAtom, update(widgetIndex, newWidget, widgets));
  },
);

export const duplicateWidgetDerivedAtom = atom(
  null,
  (get, setAtom, title: string) => {
    const widget = find(propEq('i', title), get(layoutAtom));

    if (isNil(widget)) {
      return;
    }

    setAtom(addWidgetDerivedAtom, widget.widgetConfiguration);
  },
);
