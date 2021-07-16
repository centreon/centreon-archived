import * as React from 'react';

import { useSortable } from '@dnd-kit/sortable';
import { CSS, Transform } from '@dnd-kit/utilities';
import { DraggableSyntheticListeners } from '@dnd-kit/core';
import { equals } from 'ramda';

import { DetailCardLine } from '../DetailsCard/cards';

import Item from './Item';

interface Props
  extends Pick<DetailCardLine, 'active' | 'line' | 'isCustomCard' | 'field'> {
  title: string;
  width: number;
  xs?: number;
}

interface StyledDraggableItemProps extends Props {
  isDragging?: boolean;
  listeners?: DraggableSyntheticListeners;
  setNodeRef: (node: HTMLElement | null) => void;
  style?;
  transform: Transform | null;
  transition: string | null;
  width: number;
}

const StyledDraggableItem = ({
  setNodeRef,
  transform,
  transition,
  isDragging,
  ...props
}: StyledDraggableItemProps) => {
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
    equals(prevProps.isDragging, nextProps.isDragging) &&
    equals(prevProps.active, nextProps.active) &&
    equals(prevProps.line, nextProps.line) &&
    equals(prevProps.field, nextProps.field),
);

const SortableItem = ({ title, ...props }: Props): JSX.Element => {
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
      {...props}
    />
  );
};

export default SortableItem;
