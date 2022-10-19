import { RefObject } from 'react';

import { DraggableSyntheticListeners } from '@dnd-kit/core';
import { makeStyles } from 'tss-react/mui';

import MoveIcon from '@mui/icons-material/UnfoldMore';
import { Paper } from '@mui/material';

import { Filter } from '../models';

import EditFilterCard from './EditFilterCard';

interface ContentProps extends Filter {
  attributes;
  isDragging: boolean;
  itemRef: RefObject<HTMLDivElement>;
  listeners: DraggableSyntheticListeners;
  style;
}

interface StylesProps {
  isDragging: boolean;
}

const useStyles = makeStyles<StylesProps>()((theme, { isDragging }) => ({
  filterCard: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: '1fr auto',
    padding: theme.spacing(1),
  },
  filterCardHandler: {
    cursor: isDragging ? 'grabbing' : 'grab',
  },
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
  const { classes } = useStyles({ isDragging });

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
