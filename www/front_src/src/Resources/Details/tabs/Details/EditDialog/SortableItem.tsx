import * as React from 'react';

import { useSortable } from '@dnd-kit/sortable';
import { CSS, Transform } from '@dnd-kit/utilities';
import { DraggableSyntheticListeners } from '@dnd-kit/core';
import { equals } from 'ramda';

import { GridSize } from '@material-ui/core';

import Item from './Item';

interface Props {
  title: string;
  xs?: GridSize;
}

interface ContentProps {
  isDragging?: boolean;
  listeners?: DraggableSyntheticListeners;
  setNodeRef: (node: HTMLElement | null) => void;
  style?;
  title: string;
  transform: Transform | null;
  transition: string | null;
  xs?: GridSize;
}

const StyledDraggableItem = ({
  setNodeRef,
  transform,
  transition,
  isDragging,
  ...props
}: ContentProps) => {
  const style = {
    height: '100%',
    opacity: isDragging ? '0.7' : '1',
    transform: CSS.Translate.toString(transform),
    transition,
    width: '100%',
  };

  return (
    <Item {...props} isDragging={isDragging} ref={setNodeRef} style={style} />
  );
};

const MemoizedStyledDraggableItem = React.memo(
  StyledDraggableItem,
  (prevProps, nextProps) =>
    equals(prevProps.title, nextProps.title) &&
    equals(prevProps.transform, nextProps.transform) &&
    equals(prevProps.isDragging, nextProps.isDragging),
);

const SortableItem = ({ title, xs }: Props): JSX.Element => {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({ id: title });

  return (
    <MemoizedStyledDraggableItem
      setNodeRef={setNodeRef}
      {...attributes}
      {...listeners}
      isDragging={isDragging}
      title={title}
      transform={transform}
      transition={transition}
      xs={xs}
    />
  );
};

export default SortableItem;
