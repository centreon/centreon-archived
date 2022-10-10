import { atom } from 'jotai';
import {
  equals,
  filter,
  isEmpty,
  length,
  lensProp,
  map,
  propEq,
  reject,
  set,
} from 'ramda';
import ReactGridLayout from 'react-grid-layout';

export const columnsAtom = atom(3);

export const layoutAtom = atom<Array<ReactGridLayout.Layout>>([]);

export const isEditingAtom = atom(false);

export const setLayoutModeDerivedAtom = atom(
  null,
  (get, setAtom, isEditing: boolean) => {
    setAtom(isEditingAtom, isEditing);

    const newLayout = map(set(lensProp('static'), !isEditing), get(layoutAtom));

    setAtom(layoutAtom, newLayout);
  },
);

export const addWidgetDerivedAtom = atom(null, (get, setAtom) => {
  const currentLayout = get(layoutAtom);
  const columns = get(columnsAtom);

  const title = `Widget ${length(currentLayout)}`;

  const baseWidgetLayout = {
    h: 4,
    i: title,
    minH: 4,
    static: false,
    w: 1,
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
});

export const removeWidgetDerivedAtom = atom(
  null,
  (_, setAtom, widgetKey: string) => {
    setAtom(layoutAtom, (currentLayout) =>
      reject(propEq('i', widgetKey), currentLayout),
    );
  },
);
