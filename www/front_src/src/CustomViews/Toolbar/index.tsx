import { FC } from 'react';

import { useAtomValue, useSetAtom } from 'jotai';
import { makeStyles } from 'tss-react/mui';

import { Button } from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';

import { SaveButton } from '@centreon/ui';

import { isEditingAtom, setLayoutModeDerivedAtom } from '../atoms';
import { labelEdit, labelSave } from '../translatedLabels';

import AddWidgetDialog from './AddWidgetDialog';

const useStyles = makeStyles()((theme) => ({
  toolbarButtons: {
    columnGap: theme.spacing(2),
    display: 'flex',
    flexDirection: 'row',
  },
}));

const Toolbar: FC = () => {
  const { classes } = useStyles();

  const isEditing = useAtomValue(isEditingAtom);
  const setLayoutMode = useSetAtom(setLayoutModeDerivedAtom);

  const setEditionMode = (): void => setLayoutMode(true);

  const setViewMode = (): void => setLayoutMode(false);

  return (
    <div className={classes.toolbarButtons}>
      {isEditing ? (
        <>
          <SaveButton
            labelSave={labelSave}
            size="small"
            onClick={setViewMode}
          />
          <AddWidgetDialog />
        </>
      ) : (
        <Button
          size="small"
          startIcon={<EditIcon />}
          variant="contained"
          onClick={setEditionMode}
        >
          {labelEdit}
        </Button>
      )}
    </div>
  );
};

export default Toolbar;
