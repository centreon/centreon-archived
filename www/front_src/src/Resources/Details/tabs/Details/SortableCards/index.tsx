import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { rectIntersection } from '@dnd-kit/core';
import { rectSortingStrategy } from '@dnd-kit/sortable';
import { find, isEmpty, isNil, map, pluck, propEq } from 'ramda';

import { Box, Grid } from '@material-ui/core';

import { SortbleItems, useLocaleDateTimeFormat } from '@centreon/ui';

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

interface RootComponentProps {
  children: JSX.Element | null;
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
  ).filter(({ field }) => !isNil(field) && !isEmpty(field));

  const RootComponent = ({ children }: RootComponentProps): JSX.Element => (
    <Grid container spacing={1} style={{ width: panelWidth }}>
      {children}
    </Grid>
  );

  const dragEnd = (items: Array<string>) => {
    storeDetailsCards(items);
  };

  return (
    <Box>
      <SortbleItems<CardsLayout>
        Content={Content}
        RootComponent={RootComponent}
        collisionDetection={rectIntersection}
        itemProps={[
          'field',
          'line',
          'xs',
          'active',
          'isCustomCard',
          'width',
          'title',
        ]}
        items={cards}
        sortingStrategy={rectSortingStrategy}
        onDragEnd={dragEnd}
      />
    </Box>
  );
};

export default SortableCards;
