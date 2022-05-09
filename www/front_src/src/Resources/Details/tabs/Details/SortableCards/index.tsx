import { useState } from 'react';

import { useTranslation } from 'react-i18next';
import { rectIntersection } from '@dnd-kit/core';
import { rectSortingStrategy } from '@dnd-kit/sortable';
import {
  append,
  equals,
  filter,
  find,
  findIndex,
  isEmpty,
  map,
  pluck,
  propEq,
  remove,
  difference,
  uniq,
} from 'ramda';
import { useAtom } from 'jotai';

import { Box, Grid } from '@mui/material';

import {
  SortableItems,
  useLocaleDateTimeFormat,
  RootComponentProps,
  useMemoComponent,
} from '@centreon/ui';

import getDetailCardLines, { DetailCardLine } from '../DetailsCard/cards';
import { ResourceDetails } from '../../../models';
import { detailsCardsAtom } from '../detailsCardsAtom';

import { CardsLayout, ChangeExpandedCardsProps, ExpandAction } from './models';
import Content from './Content';

interface Props {
  details: ResourceDetails;
  panelWidth: number;
}

interface MergeDefaultAndStoredCardsProps {
  defaultCards: Array<string>;
  storedCards: Array<string>;
}

const mergeDefaultAndStoredCards = ({
  defaultCards,
  storedCards,
}: MergeDefaultAndStoredCardsProps): Array<string> => {
  const differenceBetweenDefaultAndStoredCards = difference(
    defaultCards,
    storedCards,
  );

  return uniq([...storedCards, ...differenceBetweenDefaultAndStoredCards]);
};

const SortableCards = ({ panelWidth, details }: Props): JSX.Element => {
  const { toDateTime } = useLocaleDateTimeFormat();
  const { t } = useTranslation();
  const [expandedCards, setExpandedCards] = useState<Array<string>>([]);

  const [storedDetailsCards, storeDetailsCards] = useAtom(detailsCardsAtom);

  const changeExpandedCards = ({
    action,
    card,
  }: ChangeExpandedCardsProps): void => {
    if (equals(action, ExpandAction.add)) {
      setExpandedCards(append(card, expandedCards));

      return;
    }

    const expandedCardIndex = findIndex(equals(card), expandedCards);
    setExpandedCards(remove(expandedCardIndex, 1, expandedCards));
  };

  const allDetailsCards = getDetailCardLines({
    changeExpandedCards,
    details,
    expandedCards,
    t,
    toDateTime,
  });

  const allDetailsCardsTitle = pluck('title', allDetailsCards);

  const defaultDetailsCardsLayout = isEmpty(storedDetailsCards)
    ? allDetailsCardsTitle
    : mergeDefaultAndStoredCards({
        defaultCards: allDetailsCardsTitle,
        storedCards: storedDetailsCards,
      });

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

  const dragEnd = ({ items }): void => {
    storeDetailsCards(items);
  };

  return useMemoComponent({
    Component: (
      <Box>
        <SortableItems<CardsLayout>
          updateSortableItemsOnItemsChange
          Content={Content}
          RootComponent={RootComponent}
          collisionDetection={rectIntersection}
          itemProps={[
            'shouldBeDisplayed',
            'line',
            'xs',
            'isCustomCard',
            'width',
            'title',
          ]}
          items={displayedCards}
          sortingStrategy={rectSortingStrategy}
          onDragEnd={dragEnd}
        />
      </Box>
    ),
    memoProps: [panelWidth, expandedCards, details],
  });
};

export default SortableCards;
