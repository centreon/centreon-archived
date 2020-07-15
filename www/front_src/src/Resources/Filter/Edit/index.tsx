import React from 'react';

import {
  Typography,
  makeStyles,
  Paper,
  CircularProgress,
} from '@material-ui/core';
import MoveIcon from '@material-ui/icons/MoreVert';
import EditIcon from '@material-ui/icons/Edit';
import SaveIcon from '@material-ui/icons/Save';

import DeleteIcon from '@material-ui/icons/Delete';

import { RightPanel, IconButton } from '@centreon/ui';

import { contains } from 'ramda';
import { useResourceContext } from '../../Context';
import {
  labelEditFilters,
  labelRename,
  labelDelete,
} from '../../translatedLabels';

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
  filterCard: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    alignItems: 'center',
    gridTemplateColumns: '1fr 1fr 1fr',
  },
  filterEditActions: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
    justifyItems: 'center',
  },
}));

const EditFiltersPanel = (): JSX.Element | null => {
  const classes = useStyles();
  const [editingFilterIds, setEditingFilterIds] = React.useState<Array<number>>(
    [],
  );
  const [renamingFilterIds, setRenamingFilterIds] = React.useState<
    Array<number>
  >([]);
  const [deletingFilterIds, setDeletingFilterIds] = React.useState<
    Array<number>
  >([]);

  const editFilter = (id) => (): void => {
    setEditingFilterIds([...editingFilterIds, id]);
  };

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
          {customFilters?.map(({ name, id }) => {
            const editing = contains(id, editingFilterIds);

            return (
              <div className={classes.filterCard} key={id}>
                <span>{name}</span>
                <div className={classes.filterEditActions}>
                  {editing && <CircularProgress size={24} />}
                  {!editing && (
                    <>
                      <IconButton title={labelDelete} onClick={() => {}}>
                        <DeleteIcon fontSize="small" />
                      </IconButton>
                      <IconButton title={labelRename} onClick={editFilter(id)}>
                        <EditIcon fontSize="small" />
                      </IconButton>
                    </>
                  )}
                </div>
              </div>
            );
          })}
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
