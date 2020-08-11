import * as React from 'react';

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

import {
  Typography,
  makeStyles,
  LinearProgress,
  Paper,
} from '@material-ui/core';
import MoveIcon from '@material-ui/icons/UnfoldMore';

import { SectionPanel, useRequest } from '@centreon/ui';

import { move, isNil } from 'ramda';
import { useResourceContext } from '../../Context';
import { labelEditFilters } from '../../translatedLabels';
import EditFilterCard from './EditFilterCard';
import { patchFilter } from '../api';

const useStyles = makeStyles((theme) => ({
  header: {
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
  },
  container: {
    width: '100%',
  },
  loadingIndicator: {
    height: theme.spacing(1),
    width: '100%',
    marginBottom: theme.spacing(1),
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
    gridTemplateColumns: '1fr auto',
    alignItems: 'center',
    padding: theme.spacing(1),
  },
}));

const EditFiltersPanel = (): JSX.Element | null => {
  const classes = useStyles();

  const {
    editPanelOpen,
    setEditPanelOpen,
    customFilters,
    setCustomFilters,
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
    const id = Number(draggableId);

    if (isNil(destination)) {
      return;
    }

    const reordedCustomFilters = move(
      source.index,
      destination.index,
      customFilters,
    );

    setCustomFilters(reordedCustomFilters);

    sendRequest({ id, order: destination.index });
  };

  const sections = [
    {
      expandable: false,
      id: 'edit',
      section: (
        <div className={classes.container}>
          <div className={classes.loadingIndicator}>
            {sending && <LinearProgress style={{ width: '100%' }} />}
          </div>
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
                        <Paper
                          square
                          className={classes.filterCard}
                          ref={draggable.innerRef}
                          {...draggable.draggableProps}
                        >
                          <EditFilterCard filter={filter} />
                          <div {...draggable.dragHandleProps}>
                            <MoveIcon />
                          </div>
                        </Paper>
                      )}
                    </Draggable>
                  ))}
                  {droppable.placeholder}
                </div>
              )}
            </Droppable>
          </DragDropContext>
        </div>
      ),
    },
  ];

  const header = (
    <div className={classes.header}>
      <Typography variant="h5" align="center">
        {labelEditFilters}
      </Typography>
    </div>
  );

  return (
    <SectionPanel
      sections={sections}
      header={header}
      onClose={closeEditPanel}
    />
  );
};

export default EditFiltersPanel;
