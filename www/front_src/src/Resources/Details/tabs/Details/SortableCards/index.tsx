import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { rectIntersection } from '@dnd-kit/core';
import { rectSortingStrategy } from '@dnd-kit/sortable';
import { filter, find, isEmpty, isNil, map, pluck, propEq } from 'ramda';

import { Box, Grid } from '@material-ui/core';

import {
  SortableItems,
  useLocaleDateTimeFormat,
  RootComponentProps,
} from '@centreon/ui';

import getDetailCardLines, { DetailCardLine } from '../DetailsCard/cards';
import { ResourceDetails } from '../../../models';
import {
  getStoredOrDefaultDetailsCards,
  storeDetailsCards,
} from '../storedDetailsCards';

import { CardsLayout } from './models';
import Content from './Content';

interface Props {
  details: ResourceDetails;
  panelWidth: number;
}

const SortableCards = ({ panelWidth, details }: Props): JSX.Element => {
  const { t } = useTranslation();
  const { toDateTime } = useLocaleDateTimeFormat();

  const storedDetailsCards = getStoredOrDefaultDetailsCards([]);

  const allDetailsCards = getDetailCardLines({ details, t, toDateTime });

  const defaultDetailsCardsLayout = isEmpty(storedDetailsCards)
    ? pluck('title', allDetailsCards)
    : storedDetailsCards;

  const cards = map<string, CardsLayout>(
    (title) => ({
      id: title,
      width: panelWidth,
      ...(find(propEq('title', title), allDetailsCards) as DetailCardLine),
    }),
    defaultDetailsCardsLayout,
  );

  const displayedCards = filter(
    ({ shouldBeDisplayed }) => shouldBeDisplayed,
    cards,
  );
  const RootComponent = ({ children }: RootComponentProps): JSX.Element => (
    <Grid container spacing={1} style={{ width: panelWidth }}>
      {children}
    </Grid>
  );

  const dragEnd = (items: Array<string>): void => {
    storeDetailsCards(items);
  };

  return (
    <Box>
      <SortableItems<CardsLayout>
        Content={Content}
        RootComponent={RootComponent}
        collisionDetection={rectIntersection}
        itemProps={[
          'shouldBeDisplayed',
          'line',
          'xs',
          'active',
          'isCustomCard',
          'width',
          'title',
        ]}
        items={displayedCards}
        sortingStrategy={rectSortingStrategy}
        onDragEnd={dragEnd}
      />
    </Box>
  );
};

export default SortableCards;
