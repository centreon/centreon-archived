import * as React from 'react';

import { DraggableSyntheticListeners } from '@dnd-kit/core';

import MoveIcon from '@mui/icons-material/UnfoldMore';
import { Paper, Theme } from '@mui/material';
import { CreateCSSProperties, makeStyles } from '@mui/styles';

import { Filter } from '../models';

import EditFilterCard from './EditFilterCard';

interface ContentProps extends Filter {
  attributes;
  isDragging: boolean;
  itemRef: React.RefObject<HTMLDivElement>;
  listeners: DraggableSyntheticListeners;
  style;
}

const useStyles = makeStyles<Theme, { isDragging: boolean }>((theme) => ({
  filterCard: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: '1fr auto',
    padding: theme.spacing(1),
  },
  filterCardHandler: ({ isDragging }): CreateCSSProperties => ({
    cursor: isDragging ? 'grabbing' : 'grab',
  }),
}));

const SortableContent = ({
  listeners,
  attributes,
  style,
  itemRef,
  criterias,
  id,
  name,
  isDragging,
}: ContentProps): JSX.Element => {
  const classes = useStyles({ isDragging });

  return (
    <Paper
      square
      className={classes.filterCard}
      {...attributes}
      ref={itemRef}
      style={style}
    >
      <EditFilterCard filter={{ criterias, id, name }} />
      <div
        {...listeners}
        className={classes.filterCardHandler}
        role="button"
        tabIndex={0}
      >
        <MoveIcon />
      </div>
    </Paper>
  );
};

export default SortableContent;
