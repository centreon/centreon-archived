import React from 'react';

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

import { Typography, makeStyles } from '@material-ui/core';
import MoveIcon from '@material-ui/icons/MoreVert';

import { RightPanel } from '@centreon/ui';

import { useResourceContext } from '../../Context';
import { labelEditFilters } from '../../translatedLabels';
import EditFilterCard from './EditFilterCard';

const useStyles = makeStyles((theme) => ({
  header: {
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  filters: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(3),
    gridTemplateRows: '1fr',
  },
  filterCard: {
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: 'auto 1fr',
    alignItems: 'center',
  },
}));

const EditFiltersPanel = (): JSX.Element | null => {
  const classes = useStyles();

  const {
    editPanelOpen,
    setEditPanelOpen,
    customFilters,
  } = useResourceContext();

  const closeEditPanel = (): void => {
    setEditPanelOpen(false);
  };

  const onDragEnd = (params): void => {
    console.log(params);
  };

  const Sections = [
    {
      expandable: false,
      id: 'edit',
      Section: (
        <DragDropContext onDragEnd={onDragEnd}>
          <Droppable droppableId="droppable">
            {(droppable): JSX.Element => (
              <div
                className={classes.filters}
                ref={droppable.innerRef}
                {...droppable.droppableProps}
              >
                {customFilters?.map((filter) => (
                  <Draggable
                    key={filter.id}
                    draggableId={`filter${filter.id}`}
                    index={filter.id}
                  >
                    {(draggable): JSX.Element => (
                      <div
                        className={classes.filterCard}
                        ref={draggable.innerRef}
                        {...draggable.draggableProps}
                      >
                        <div {...draggable.dragHandleProps}>
                          <MoveIcon />
                        </div>
                        <EditFilterCard filter={filter} />
                      </div>
                    )}
                  </Draggable>
                ))}
                {droppable.placeholder}
              </div>
            )}
          </Droppable>
        </DragDropContext>
      ),
    },
  ];

  const Header = (
    <div className={classes.header}>
      <Typography variant="h5" align="center">
        {labelEditFilters}
      </Typography>
    </div>
  );

  if (!editPanelOpen) {
    return null;
  }

  return (
    <RightPanel
      active
      Sections={Sections}
      Header={Header}
      onClose={closeEditPanel}
    />
  );
};

export default EditFiltersPanel;
