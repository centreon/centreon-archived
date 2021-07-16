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
  isEmpty,
  isNil,
  move,
  not,
  path,
  pipe,
  pluck,
  propEq,
} from 'ramda';

import { Box, Grid, useTheme } from '@material-ui/core';

import { useLocaleDateTimeFormat } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import getDetailCardLines, { DetailCardLine } from '../DetailsCard/cards';
import { ResourceDetails } from '../../../models';
import {
  getStoredOrDefaultDetailsCards,
  storeDetailsCards,
} from '../storedDetailsCards';

import SortableItem from './SortableItem';
import Item from './Item';

interface Props {
  details: ResourceDetails;
  panelWidth: number;
}

const SortableCards = ({ panelWidth, details }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { toDateTime } = useLocaleDateTimeFormat();
  const theme = useTheme();

  const storedDetailsCards = getStoredOrDefaultDetailsCards([]);

  const allDetailsCards = getDetailCardLines({ details, t, toDateTime });

  const defaultDetailsCardsLayout = isEmpty(storedDetailsCards)
    ? pluck('title', allDetailsCards)
    : storedDetailsCards;

  const [activeId, setActiveId] = React.useState<string | null>(null);
  const [detailsCardItems, setDetailsCardItems] = React.useState(
    defaultDetailsCardsLayout,
  );
  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  const dragStart = (event): void => {
    setActiveId(path(['active', 'id'], event) as string);
  };

  const dragCancel = () => setActiveId(null);

  const dragEnd = () => {
    setActiveId(null);

    storeDetailsCards(detailsCardItems);
  };

  const dragOver = (event): void => {
    const overId = path(['over', 'id'], event);

    if (
      pipe(isNil, not)(overId) &&
      pipe(equals(activeId), not)(overId as string | null)
    ) {
      const oldIndex = indexOf(activeId, detailsCardItems);
      const newIndex = indexOf(overId, detailsCardItems);

      const newCardsOrder = move<string>(oldIndex, newIndex, detailsCardItems);
      setDetailsCardItems(newCardsOrder);
    }
  };

  const activeDetailsCardLine = find(
    propEq('title', activeId),
    allDetailsCards,
  );

  return (
    <Box>
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
              const { field, line, xs, active, isCustomCard } = find(
                propEq('title', detailsCardItem),
                allDetailsCards,
              ) as DetailCardLine;
              const displayCard = !isNil(field) && !isEmpty(field);

              return (
                displayCard && (
                  <SortableItem
                    active={active}
                    isCustomCard={isCustomCard}
                    key={detailsCardItem}
                    line={line as JSX.Element}
                    title={detailsCardItem}
                    width={panelWidth}
                    xs={xs || 6}
                  />
                )
              );
            })}
          </Grid>
        </SortableContext>
        <DragOverlay style={{ zIndex: theme.zIndex.tooltip }}>
          <Grid container spacing={1} style={{ width: panelWidth }}>
            {activeId ? (
              <Item
                isDragging
                active={activeDetailsCardLine?.active}
                isCustomCard={activeDetailsCardLine?.isCustomCard}
                line={activeDetailsCardLine?.line as JSX.Element}
                title={activeId}
                width={panelWidth}
                xs={activeDetailsCardLine?.xs}
              />
            ) : null}
          </Grid>
        </DragOverlay>
      </DndContext>
    </Box>
  );
};

export default SortableCards;
