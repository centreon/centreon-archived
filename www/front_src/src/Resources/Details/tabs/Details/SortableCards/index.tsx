import * as React from 'react';

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
} from 'ramda';

import { Box, Grid } from '@material-ui/core';

import {
  SortableItems,
  useLocaleDateTimeFormat,
  RootComponentProps,
  useMemoComponent,
} from '@centreon/ui';

import getDetailCardLines, { DetailCardLine } from '../DetailsCard/cards';
import { ResourceDetails } from '../../../models';
import {
  getStoredOrDefaultDetailsCards,
  storeDetailsCards,
} from '../storedDetailsCards';

import { CardsLayout, ChangeExpandedCardsProps, ExpandAction } from './models';
import Content from './Content';

interface Props {
  details: ResourceDetails;
  panelWidth: number;
}

const SortableCards = ({ panelWidth, details }: Props): JSX.Element => {
  const { toDateTime } = useLocaleDateTimeFormat();
  const { t } = useTranslation();
  const [expandedCards, setExpandedCards] = React.useState<Array<string>>([]);

  const storedDetailsCards = getStoredOrDefaultDetailsCards([]);

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

  return useMemoComponent({
    Component: (
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
    ),
    memoProps: [defaultDetailsCardsLayout, panelWidth, expandedCards],
  });
};

export default SortableCards;
