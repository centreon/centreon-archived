import { FC } from 'react';

import { makeStyles } from 'tss-react/mui';
import { useAtomValue, useSetAtom } from 'jotai';

import { Card, CardHeader } from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';

import { IconButton, useMemoComponent } from '@centreon/ui';

import { isEditingAtom, removeWidgetDerivedAtom } from '../atoms';

interface Props {
  title: string;
}

const useStyles = makeStyles()({
  widgetContainer: {
    height: '100%',
    width: '100%',
  },
});

const Widget: FC<Props> = ({ title }) => {
  const { classes } = useStyles();

  const isEditing = useAtomValue(isEditingAtom);
  const removeWidget = useSetAtom(removeWidgetDerivedAtom);

  const remove = (): void => removeWidget(title);

  return useMemoComponent({
    Component: (
      <Card className={classes.widgetContainer}>
        <CardHeader
          action={
            isEditing && (
              <IconButton onClick={remove}>
                <CloseIcon />
              </IconButton>
            )
          }
          title={title}
        />
      </Card>
    ),
    memoProps: [isEditing, title],
  });
};

export default Widget;
