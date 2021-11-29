import * as React from 'react';

import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import { useTranslation } from 'react-i18next';
import { move, isNil } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import {
  Typography,
  makeStyles,
  LinearProgress,
  Paper,
} from '@material-ui/core';
import MoveIcon from '@material-ui/icons/UnfoldMore';

import { MemoizedSectionPanel as SectionPanel, useRequest } from '@centreon/ui';

import { labelEditFilters } from '../../translatedLabels';
import { patchFilter } from '../api';
import { customFiltersAtom, editPanelOpenAtom } from '../filterAtoms';

import EditFilterCard from './EditFilterCard';

const useStyles = makeStyles((theme) => ({
  container: {
    width: '100%',
  },
  filterCard: {
    alignItems: 'center',
    display: 'grid',
    gridGap: theme.spacing(2),
    gridTemplateColumns: '1fr auto',
    padding: theme.spacing(1),
  },
  filters: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(3),
    gridTemplateRows: '1fr',
    width: '100%',
  },
  header: {
    alignItems: 'center',
    display: 'flex',
    height: '100%',
    justifyContent: 'center',
  },
  loadingIndicator: {
    height: theme.spacing(1),
    marginBottom: theme.spacing(1),
    width: '100%',
  },
}));

const EditFiltersPanel = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { sendRequest, sending } = useRequest({
    request: patchFilter,
  });

  const [customFilters, setCustomFilters] = useAtom(customFiltersAtom);
  const setEditPanelOpen = useUpdateAtom(editPanelOpenAtom);

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
                      draggableId={`${filter.id}`}
                      index={index}
                      key={filter.id}
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
      <Typography align="center" variant="h6">
        {t(labelEditFilters)}
      </Typography>
    </div>
  );

  return (
    <SectionPanel
      header={header}
      memoProps={[customFilters]}
      sections={sections}
      onClose={closeEditPanel}
    />
  );
};

export default EditFiltersPanel;
