import { FC, useState } from 'react';

import { find, isNil, map, path, pathEq } from 'ramda';
import { useSetAtom } from 'jotai';
import { makeStyles } from 'tss-react/mui';

import { Button } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';

import { Dialog, SelectField } from '@centreon/ui';

import {
  labelAdd,
  labelAddAWidget,
  labelAddWidget,
  labelCancel,
} from '../translatedLabels';
import useFederatedWidgets from '../../federatedModules/useFedaratedWidgets';
import { FederatedModule } from '../../federatedModules/models';
import { addWidgetDerivedAtom } from '../atoms';

const useStyles = makeStyles()((theme) => ({
  selectField: {
    minWidth: theme.spacing(20),
  },
}));

const AddWidgetDialog: FC = () => {
  const { classes } = useStyles();

  const [addWidgetDialogOpened, setAddWidgetDialogOpened] = useState(false);
  const [selectedWidget, setSelectedWidget] = useState<FederatedModule | null>(
    null,
  );

  const addWidget = useSetAtom(addWidgetDerivedAtom);

  const { federatedWidgets } = useFederatedWidgets();

  const open = (): void => setAddWidgetDialogOpened(true);

  const close = (): void => {
    setAddWidgetDialogOpened(false);
    setSelectedWidget(null);
  };

  const confirm = (): void => {
    if (isNil(selectedWidget)) {
      return;
    }

    addWidget({
      moduleName: selectedWidget?.moduleName,
      path: selectedWidget?.federatedComponentsConfiguration.path,
      widgetMinHeight:
        selectedWidget?.federatedComponentsConfiguration.widgetMinHeight,
      widgetMinWidth:
        selectedWidget?.federatedComponentsConfiguration.widgetMinWidth,
    });
    close();
  };

  const selectWidget = (event): void => {
    const value = path(['target', 'value'], event);

    const widget = find(
      pathEq(['federatedComponentsConfiguration', 'path'], value),
      federatedWidgets || [],
    );

    setSelectedWidget(widget || null);
  };

  const widgetsAvailable = map(
    ({ moduleName, federatedComponentsConfiguration }) => ({
      id: federatedComponentsConfiguration?.path,
      name: moduleName,
    }),
    federatedWidgets || [],
  );

  return (
    <>
      <Button size="small" startIcon={<AddIcon />} onClick={open}>
        {labelAddWidget}
      </Button>
      <Dialog
        confirmDisabled={isNil(selectWidget)}
        labelCancel={labelCancel}
        labelConfirm={labelAdd}
        labelTitle={labelAddAWidget}
        open={addWidgetDialogOpened}
        onCancel={close}
        onClose={close}
        onConfirm={confirm}
      >
        <SelectField
          className={classes.selectField}
          options={widgetsAvailable}
          selectedOptionId={
            selectedWidget?.federatedComponentsConfiguration?.path || ''
          }
          onChange={selectWidget}
        />
      </Dialog>
    </>
  );
};

export default AddWidgetDialog;
