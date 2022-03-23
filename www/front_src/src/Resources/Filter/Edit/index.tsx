import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { map, find, equals, path } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';
import { rectIntersection } from '@dnd-kit/core';
import { rectSortingStrategy } from '@dnd-kit/sortable';

import { Typography, LinearProgress, Stack } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import {
  MemoizedSectionPanel as SectionPanel,
  useRequest,
  SortableItems,
} from '@centreon/ui';

import { labelEditFilters } from '../../translatedLabels';
import { patchFilter } from '../api';
import { customFiltersAtom, editPanelOpenAtom } from '../filterAtoms';
import { Filter } from '../models';
import { Criteria } from '../Criterias/models';

import SortableContent from './SortableContent';

const useStyles = makeStyles((theme) => ({
  container: {
    width: '100%',
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

  const dragEnd = (items: Array<string>, event): void => {
    const reorderedCutomFilters = map((id) => {
      const filter = find(
        (customFilter) => equals(Number(customFilter.id), Number(id)),
        customFilters,
      ) as Filter;

      return {
        ...filter,
        order: items.indexOf(id),
      };
    }, items);

    setCustomFilters(reorderedCutomFilters);

    const activeId = path(['active', 'id'], event);
    const destinationIndex = path(
      ['active', 'data', 'current', 'sortable', 'index'],
      event,
    ) as number;

    sendRequest({ id: activeId, order: destinationIndex + 1 });
  };

  const displayedFilters = map(
    ({ id, ...other }) => ({ ...other, id: `${id}` }),
    customFilters,
  );

  const RootComponent = React.useCallback(
    ({ children }: RootComponentProps): JSX.Element => (
      <Stack spacing={2}>{children}</Stack>
    ),
    [],
  );

  const sections = [
    {
      expandable: false,
      id: 'edit',
      section: (
        <div className={classes.container}>
          <div className={classes.loadingIndicator}>
            {sending && <LinearProgress style={{ width: '100%' }} />}
          </div>
          <SortableItems<{
            criterias: Array<Criteria>;
            id: string;
            name: string;
          }>
            updateSortableItemsOnItemsChange
            Content={SortableContent}
            RootComponent={RootComponent}
            collisionDetection={rectIntersection}
            itemProps={['criterias', 'id', 'name']}
            items={displayedFilters}
            sortingStrategy={rectSortingStrategy}
            onDragEnd={dragEnd}
          />
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
