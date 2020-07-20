import * as React from 'react';

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

import { Typography, makeStyles } from '@material-ui/core';
import MoveIcon from '@material-ui/icons/MoreVert';

import { RightPanel, useRequest } from '@centreon/ui';

import { find, pipe, propEq } from 'ramda';
import { useResourceContext } from '../../Context';
import { labelEditFilters } from '../../translatedLabels';
import EditFilterCard from './EditFilterCard';
import { patchFilter } from '../api';
import { Filter } from '../models';

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
    width: '100%',
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
    loadCustomFilters,
  } = useResourceContext();

  const { sendRequest, sending } = useRequest({
    request: patchFilter,
  });

  if (!editPanelOpen) {
    return null;
  }

  const closeEditPanel = (): void => {
    setEditPanelOpen(false);
  };

  const onDragEnd = ({ draggableId, source, destination }): void => {
    // sendRequest()
    // console.log(params);

    const id = Number(draggableId) as string | number;
    const draggedFilter = find(
      propEq('id', id),
      customFilters as Array<Filter>,
    );

    sendRequest({ ...draggedFilter, order: destination.index }).then(() => {
      loadCustomFilters();
    });
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
                {customFilters?.map((filter, index) => (
                  <Draggable
                    key={filter.id}
                    draggableId={`${filter.id}`}
                    index={index}
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
