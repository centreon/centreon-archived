import * as React from 'react';

import { useTranslation } from 'react-i18next';
import {
  rectIntersection,
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import {
  SortableContext,
  sortableKeyboardCoordinates,
  rectSortingStrategy,
} from '@dnd-kit/sortable';
import {
  equals,
  find,
  indexOf,
  isNil,
  move,
  not,
  path,
  pipe,
  pluck,
  propEq,
} from 'ramda';

import EditIcon from '@material-ui/icons/Edit';
import { Box, Grid, makeStyles } from '@material-ui/core';

import { Dialog, IconButton } from '@centreon/ui';

import {
  labelCancel,
  labelEditDetailsTiles,
  labelSave,
} from '../../../../translatedLabels';
import { DetailCardLine } from '../DetailsCard/cards';

import SortableItem from './SortableItem';
import Item from './Item';

interface Props {
  detailsCards: Array<DetailCardLine>;
}

const useStyles = makeStyles({
  container: {
    height: '60vh',
    overflowX: 'hidden',
    overflowY: 'auto',
  },
});

const EditDialog = ({ detailsCards }: Props): JSX.Element => {
  const [activeId, setActiveId] = React.useState<string | null>(null);
  const [detailsTilesEditionOpened, setDetailsTilesEditionOpened] =
    React.useState(false);
  const [detailsCardItems, setDetailsCardItems] = React.useState(
    pluck('title', detailsCards),
  );
  const { t } = useTranslation();
  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );
  const classes = useStyles();

  const openDetailsTilesEdition = () => setDetailsTilesEditionOpened(true);

  const closeDetailsTilesEdition = () => setDetailsTilesEditionOpened(false);

  const confirmSave = () => {
    console.log('saved');
    closeDetailsTilesEdition();
  };

  const dragStart = (event): void => {
    setActiveId(path(['active', 'id'], event) as string);
  };

  const dragCancel = () => setActiveId(null);

  const dragEnd = () => setActiveId(null);

  const dragOver = (event): void => {
    const overId = path(['over', 'id'], event);

    if (
      pipe(isNil, not)(overId) &&
      pipe(equals(activeId), not)(overId as string | null)
    ) {
      const oldIndex = indexOf(activeId, detailsCardItems);
      const newIndex = indexOf(overId, detailsCardItems);
      setDetailsCardItems(move<string>(oldIndex, newIndex, detailsCardItems));
    }
  };

  const activeDetailsCardLine = find(propEq('title', activeId), detailsCards);

  return (
    <>
      <IconButton
        title={t(labelEditDetailsTiles)}
        onClick={openDetailsTilesEdition}
      >
        <EditIcon fontSize="small" />
      </IconButton>
      <Dialog
        fullWidth
        labelCancel={t(labelCancel)}
        labelConfirm={t(labelSave)}
        labelTitle={t(labelEditDetailsTiles)}
        maxWidth="xs"
        open={detailsTilesEditionOpened}
        onCancel={closeDetailsTilesEdition}
        onClose={closeDetailsTilesEdition}
        onConfirm={confirmSave}
      >
        <Box className={classes.container}>
          <DndContext
            collisionDetection={rectIntersection}
            sensors={sensors}
            onDragCancel={dragCancel}
            onDragEnd={dragEnd}
            onDragOver={dragOver}
            onDragStart={dragStart}
          >
            <SortableContext
              items={detailsCardItems}
              strategy={rectSortingStrategy}
            >
              <Grid container spacing={1}>
                {detailsCardItems.map((detailsCardItem) => {
                  const detailsCardLine = find(
                    propEq('title', detailsCardItem),
                    detailsCards,
                  );

                  return (
                    <SortableItem
                      key={detailsCardItem}
                      title={detailsCardItem}
                      xs={detailsCardLine?.xs || 6}
                    />
                  );
                })}
              </Grid>
            </SortableContext>
            <DragOverlay>
              <Grid container spacing={1} style={{ width: '380px' }}>
                {activeId ? (
                  <Item
                    isDragging
                    title={activeId}
                    xs={activeDetailsCardLine?.xs}
                  />
                ) : null}
              </Grid>
            </DragOverlay>
          </DndContext>
        </Box>
      </Dialog>
    </>
  );
};

export default EditDialog;
