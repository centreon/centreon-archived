import React from 'react';

import { Typography, makeStyles } from '@material-ui/core';

import { RightPanel } from '@centreon/ui';

import { useResourceContext } from '../../Context';
import { labelEditFilters } from '../../translatedLabels';
import EditFilterCard from './EditFilterCard';

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
  },
}));

const EditFiltersPanel = (): JSX.Element | null => {
  const classes = useStyles();

  const {
    editPanelOpen,
    setEditPanelOpen,
    customFilters,
  } = useResourceContext();

  const closeEditPanel = (): void => {
    setEditPanelOpen(false);
  };

  const Sections = [
    {
      expandable: false,
      id: 'edit',
      Section: (
        <div className={classes.filters}>
          {customFilters?.map((filter) => (
            <EditFilterCard key={filter.id} filter={filter} />
          ))}
        </div>
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

  if (!editPanelOpen) {
    return null;
  }

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
